<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\MpesaPayment;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentMatchingService
{
    public function ingest(Business $business, array $data, array $payload = []): MpesaPayment
    {
        return DB::transaction(function () use ($business, $data, $payload): MpesaPayment {
            $payment = MpesaPayment::query()->firstOrCreate(
                ['receipt_number' => $data['receipt_number']],
                [
                    'business_id' => $business->id,
                    'amount' => $data['amount'],
                    'phone' => $data['phone'] ?? null,
                    'bill_ref_number' => $data['bill_ref_number'] ?? null,
                    'transacted_at' => $data['transacted_at'] ?? null,
                    'status' => 'unmatched',
                    'callback_payload' => $payload,
                ],
            );

            abort_unless($payment->business_id === $business->id, 404);

            if (!$payment->wasRecentlyCreated) {
                return $payment;
            }

            $openInvoices = $business->invoices()
                ->with('customer')
                ->whereIn('status', ['unpaid', 'partial'])
                ->orderBy('created_at')
                ->get();

            $this->matchByReference($payment);

            if ($payment->fresh()->status !== 'matched' && $payment->fresh()->status !== 'exception') {
                $this->matchByPhone($payment, $openInvoices);
            }

            if ($payment->fresh()->status !== 'matched' && $payment->fresh()->status !== 'exception') {
                $this->matchByAmountAndTime($payment, $openInvoices);
            }

            return $payment->fresh(['invoice', 'matchAttempts']);
        });
    }

    public function resolve(MpesaPayment $payment, Invoice $invoice): MpesaPayment
    {
        return DB::transaction(function () use ($payment, $invoice): MpesaPayment {
            $lockedPayment = MpesaPayment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $lockedInvoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            abort_unless($lockedPayment->business_id === $lockedInvoice->business_id, 404);
            abort_unless(in_array($lockedPayment->status, ['unmatched', 'exception'], true), 422, 'This payment is already matched.');

            $this->applyMatch($lockedPayment, $lockedInvoice, 'manual', 100);

            return $lockedPayment->fresh(['invoice', 'matchAttempts']);
        });
    }

    private function matchByReference(MpesaPayment $payment): void
    {
        if (!$payment->bill_ref_number) {
            $this->attempt($payment, null, 'reference', 0, 'no_candidate');
            return;
        }

        $invoice = Invoice::query()
            ->where('business_id', $payment->business_id)
            ->where('mpesa_reference', $payment->bill_ref_number)
            ->first();

        if (!$invoice) {
            $this->attempt($payment, null, 'reference', 0, 'no_candidate');
            return;
        }

        if (!in_array($invoice->status, ['unpaid', 'partial'], true)) {
            $this->attempt($payment, $invoice, 'reference', 100, 'invoice_not_open');
            $payment->update(['status' => 'exception', 'match_method' => 'reference', 'match_confidence' => 100]);
            return;
        }

        if ((float) $payment->amount > $this->balance($invoice)) {
            $this->attempt($payment, $invoice, 'reference', 100, 'overpayment');
            $payment->update(['status' => 'exception', 'match_method' => 'reference', 'match_confidence' => 100]);
            return;
        }

        $this->applyMatch($payment, $invoice, 'reference', 100);
    }

    private function matchByPhone(MpesaPayment $payment, Collection $invoices): void
    {
        $phone = $this->normalizePhone($payment->phone);
        $candidates = $phone === '' ? collect() : $invoices->filter(
            fn (Invoice $invoice): bool => $this->normalizePhone($invoice->customer?->phone) === $phone
        )->values();

        if ($candidates->isEmpty()) {
            $this->attempt($payment, null, 'phone', 0, 'no_candidate');
            return;
        }

        $exactBalance = $candidates->filter(fn (Invoice $invoice): bool => abs($this->balance($invoice) - (float) $payment->amount) < 0.01);

        if ($exactBalance->count() === 1) {
            $this->applyMatch($payment, $exactBalance->first(), 'phone', 90);
            return;
        }

        if ($candidates->count() === 1 && (float) $payment->amount <= $this->balance($candidates->first())) {
            $this->applyMatch($payment, $candidates->first(), 'phone', 85);
            return;
        }

        foreach ($candidates as $candidate) {
            $this->attempt($payment, $candidate, 'phone', 50, 'ambiguous');
        }

        $payment->update(['status' => 'exception', 'match_method' => 'phone']);
    }

    private function matchByAmountAndTime(MpesaPayment $payment, Collection $invoices): void
    {
        $transactedAt = $payment->transacted_at;
        $candidates = $transactedAt ? $invoices->filter(function (Invoice $invoice) use ($payment, $transactedAt): bool {
            $ageInDays = $invoice->created_at->diffInDays($transactedAt, false);

            return $ageInDays >= 0
                && $ageInDays <= 30
                && abs($this->balance($invoice) - (float) $payment->amount) < 0.01;
        })->values() : collect();

        if ($candidates->count() === 1) {
            $this->applyMatch($payment, $candidates->first(), 'amount_time', 80);
            return;
        }

        if ($candidates->count() > 1) {
            foreach ($candidates as $candidate) {
                $this->attempt($payment, $candidate, 'amount_time', 50, 'ambiguous');
            }

            $payment->update(['status' => 'exception', 'match_method' => 'amount_time']);
            return;
        }

        $this->attempt($payment, null, 'amount_time', 0, 'no_candidate');
    }

    private function applyMatch(MpesaPayment $payment, Invoice $invoice, string $method, float $confidence): void
    {
        $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();
        $balance = $this->balance($invoice);

        if ((float) $payment->amount > $balance) {
            $this->attempt($payment, $invoice, $method, $confidence, 'overpayment');
            $payment->update(['status' => 'exception', 'match_method' => $method, 'match_confidence' => $confidence]);
            return;
        }

        $paid = round((float) $invoice->amount_paid + (float) $payment->amount, 2);
        $fullyPaid = $paid >= (float) $invoice->amount;
        $invoice->update([
            'amount_paid' => number_format($paid, 2, '.', ''),
            'status' => $fullyPaid ? 'paid' : 'partial',
            'paid_at' => $fullyPaid ? now() : null,
        ]);

        $payment->update([
            'invoice_id' => $invoice->id,
            'status' => 'matched',
            'match_method' => $method,
            'match_confidence' => $confidence,
        ]);

        $this->attempt($payment, $invoice, $method, $confidence, 'matched');
    }

    private function attempt(MpesaPayment $payment, ?Invoice $invoice, string $strategy, float $confidence, string $outcome): void
    {
        $payment->matchAttempts()->create([
            'invoice_id' => $invoice?->id,
            'strategy' => $strategy,
            'confidence' => $confidence,
            'outcome' => $outcome,
        ]);
    }

    private function balance(Invoice $invoice): float
    {
        return round((float) $invoice->amount - (float) $invoice->amount_paid, 2);
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '') ?? '';

        if (str_starts_with($digits, '0')) {
            return '254'.substr($digits, 1);
        }

        if (str_starts_with($digits, '254')) {
            return $digits;
        }

        return $digits;
    }
}