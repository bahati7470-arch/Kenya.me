<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MpesaPayment;
use App\Services\PaymentMatchingService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController
{
    public function index(Request $request): View
    {
        $payments = MpesaPayment::query()
            ->with('invoice.customer')
            ->where('business_id', $request->user()->business_id)
            ->whereIn('status', ['unmatched', 'exception'])
            ->latest('transacted_at')
            ->paginate(20);

        return view('payments.index', ['payments' => $payments]);
    }

    public function create(): View
    {
        return view('payments.create');
    }

    public function store(Request $request, PaymentMatchingService $matching): RedirectResponse
    {
        $validated = $request->validate([
            'receipt_number' => ['required', 'string', 'max:32'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'phone' => ['nullable', 'string', 'max:32'],
            'bill_ref_number' => ['nullable', 'string', 'max:64'],
            'transacted_at' => ['required', 'date'],
        ]);

        $payment = $matching->ingest($request->user()->business, [
            ...$validated,
            'transacted_at' => Carbon::parse($validated['transacted_at'], 'Africa/Nairobi'),
        ], ['source' => 'manual']);

        return redirect()->route('payments.show', $payment);
    }

    public function show(Request $request, MpesaPayment $payment): View
    {
        abort_unless($payment->business_id === $request->user()->business_id, 404);

        return view('payments.show', [
            'payment' => $payment->load('invoice.customer', 'matchAttempts.invoice'),
            'invoices' => Invoice::query()
                ->where('business_id', $request->user()->business_id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->with('customer')
                ->orderBy('created_at')
                ->get(),
        ]);
    }

    public function resolve(Request $request, MpesaPayment $payment, PaymentMatchingService $matching): RedirectResponse
    {
        abort_unless($payment->business_id === $request->user()->business_id, 404);
        $validated = $request->validate(['invoice_id' => ['required', 'integer']]);
        $invoice = Invoice::query()
            ->where('business_id', $request->user()->business_id)
            ->findOrFail($validated['invoice_id']);

        $matching->resolve($payment, $invoice);

        return redirect()->route('payments.show', $payment)->with('status', 'Payment matched to invoice.');
    }
}