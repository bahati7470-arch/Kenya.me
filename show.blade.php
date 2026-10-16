@extends('layouts.app')

@section('title', 'Quotation '.$quote->quote_number)

@section('content')
    <p class="eyebrow">Quotation · {{ ucfirst($quote->status) }}</p>
    <h1>{{ $quote->customer->name }}</h1>
    <p class="intro">{{ $quote->quote_number }} · {{ $quote->description }}</p>
    <section class="panel" style="max-width:760px">
        <div class="summary">
            <div class="summary-row"><span>Quantity</span><strong>{{ number_format((float) $quote->quantity, 2) }}</strong></div>
            <div class="summary-row"><span>Unit rate</span><strong>KES {{ number_format((float) $quote->unit_price, 2) }}</strong></div>
            <div class="summary-row"><span>Markup</span><strong>{{ number_format((float) $quote->markup_percent, 2) }}%</strong></div>
            <div class="summary-row"><span>Discount</span><strong>{{ number_format((float) $quote->discount_percent, 2) }}%</strong></div>
            <div class="summary-row"><span>Valid until</span><strong>{{ $quote->valid_until?->format('d M Y') ?? 'No expiry' }}</strong></div>
            <div class="summary-row"><span>Total</span><strong class="amount">KES {{ number_format((float) $quote->amount, 2) }}</strong></div>
        </div>
        <a class="submit" href="{{ route('quotes.pdf', $quote) }}" style="text-decoration:none">Download PDF</a>
        @if ($quote->invoice)
            <p>Converted to invoice <a href="{{ route('invoices.show', $quote->invoice) }}">{{ $quote->invoice->mpesa_reference }}</a></p>
        @else
            <form method="post" action="{{ route('quotes.convert', $quote) }}">@csrf<button class="submit" type="submit">Convert to invoice</button></form>
        @endif
    </section>
@endsection