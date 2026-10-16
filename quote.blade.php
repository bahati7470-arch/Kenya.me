<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { color: #172522; font: 12px DejaVu Sans, sans-serif; }
        .header { border-bottom: 3px solid #087f5b; padding-bottom: 18px; }
        h1 { margin: 0; font-size: 28px; }
        .muted { color: #64716c; }
        table { width: 100%; border-collapse: collapse; margin-top: 28px; }
        th, td { padding: 12px 8px; border-bottom: 1px solid #d8e0da; text-align: left; }
        th { color: #64716c; font-size: 10px; text-transform: uppercase; }
        .total { margin-top: 25px; text-align: right; font-size: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header"><h1>{{ $quote->business->name }}</h1><p class="muted">QUOTATION {{ $quote->quote_number }}</p></div>
    <p><strong>Prepared for:</strong> {{ $quote->customer->name }}<br>{{ $quote->customer->phone }}<br>{{ $quote->customer->email }}</p>
    <table><thead><tr><th>Description</th><th>Quantity</th><th>Rate</th><th>Markup</th><th>Discount</th></tr></thead><tbody><tr><td>{{ $quote->description }}</td><td>{{ number_format((float) $quote->quantity, 2) }}</td><td>KES {{ number_format((float) $quote->unit_price, 2) }}</td><td>{{ number_format((float) $quote->markup_percent, 2) }}%</td><td>{{ number_format((float) $quote->discount_percent, 2) }}%</td></tr></tbody></table>
    <p class="total">Total: KES {{ number_format((float) $quote->amount, 2) }}</p>
    <p class="muted">Valid until {{ $quote->valid_until?->format('d M Y') ?? 'further notice' }}.</p>
</body>
</html>