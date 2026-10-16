@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <p class="eyebrow">{{ auth()->user()->business->name }}</p>
    <h1>Business overview</h1>
    <p class="intro">Today’s payment activity and open customer balances.</p>
    <div class="workspace">
        <section class="panel">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
                <h2>Recent invoices</h2>
                <a class="hint" href="{{ route('exports.invoices') }}">Export CSV</a>
            </div>
            @forelse ($recentInvoices as $invoice)
                <div class="summary-row">
                    <span><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->mpesa_reference }}</a><br>{{ $invoice->customer->name }}</span>
                    <strong>KES {{ number_format((float) $invoice->amount, 2) }} · {{ ucfirst($invoice->status) }}</strong>
                </div>
            @empty
                <p class="hint">No invoices yet.</p>
            @endforelse
            <a class="submit" href="{{ route('invoices.create') }}" style="text-decoration:none">Create invoice</a>
            <a class="submit" href="{{ route('quotes.create') }}" style="text-decoration:none">Create quotation</a>
        </section>
        <aside class="side-note">
            <h2>Today</h2>
            <div class="summary-row"><span>Open invoices</span><strong>{{ $openInvoices }}</strong></div>
            <div class="summary-row"><span>Outstanding</span><strong>KES {{ number_format((float) $outstanding, 2) }}</strong></div>
            <div class="summary-row"><span>M-Pesa received</span><strong>KES {{ number_format((float) $paymentsToday, 2) }}</strong></div>
            <div class="summary-row"><span>Needs review</span><strong>{{ $unmatchedCount }}</strong></div>
            <div class="summary-row"><span>Open quotations</span><strong>{{ $quoteCount }}</strong></div>
            <a href="{{ route('payments.index') }}" class="submit" style="text-decoration:none">Review payments</a>
            <a href="{{ route('exports.payments') }}" class="hint">Export payment CSV</a>
            @if ($recentPayments->isNotEmpty())
                <h2 style="margin-top:22px">Latest exceptions</h2>
                @foreach ($recentPayments as $payment)
                    <div class="summary-row"><a href="{{ route('payments.show', $payment) }}">{{ $payment->receipt_number }}</a><strong>KES {{ number_format((float) $payment->amount, 2) }}</strong></div>
                @endforeach
            @endif
        </aside>
    </div>
@endsection