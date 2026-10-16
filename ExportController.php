<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MpesaPayment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController
{
    public function invoices(Request $request): StreamedResponse
    {
        $businessId = $request->user()->business_id;
        $invoices = Invoice::query()->with('customer')->where('business_id', $businessId)->orderBy('created_at')->get();

        return response()->streamDownload(function () use ($invoices): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Invoice', 'M-Pesa reference', 'Customer', 'Phone', 'Description', 'Amount (KES)', 'Paid (KES)', 'Balance (KES)', 'Status', 'Due date', 'Created']);

            foreach ($invoices as $invoice) {
                fputcsv($output, [
                    $invoice->id,
                    $this->spreadsheetSafe($invoice->mpesa_reference),
                    $this->spreadsheetSafe($invoice->customer->name),
                    $this->spreadsheetSafe($invoice->customer->phone),
                    $this->spreadsheetSafe($invoice->description),
                    $invoice->amount,
                    $invoice->amount_paid,
                    number_format((float) $invoice->amount - (float) $invoice->amount_paid, 2, '.', ''),
                    $invoice->status,
                    $invoice->due_date?->toDateString(),
                    $invoice->created_at->toDateTimeString(),
                ]);
            }

            fclose($output);
        }, 'kenya-me-invoices.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function payments(Request $request): StreamedResponse
    {
        $payments = MpesaPayment::query()
            ->with('invoice')
            ->where('business_id', $request->user()->business_id)
            ->orderBy('transacted_at')
            ->get();

        return response()->streamDownload(function () use ($payments): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Receipt', 'Date', 'Phone', 'Account reference', 'Amount (KES)', 'Invoice', 'Match method', 'Confidence', 'Status']);

            foreach ($payments as $payment) {
                fputcsv($output, [
                    $this->spreadsheetSafe($payment->receipt_number),
                    $payment->transacted_at?->toDateTimeString(),
                    $this->spreadsheetSafe($payment->phone),
                    $this->spreadsheetSafe($payment->bill_ref_number),
                    $payment->amount,
                    $payment->invoice?->mpesa_reference,
                    $payment->match_method,
                    $payment->match_confidence,
                    $payment->status,
                ]);
            }

            fclose($output);
        }, 'kenya-me-payments.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function spreadsheetSafe(?string $value): ?string
    {
        if ($value !== null && preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'".$value;
        }

        return $value;
    }
}