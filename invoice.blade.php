<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { color: #172522; font: 12px DejaVu Sans, sans-serif; }
        .header { border-bottom: 3px solid #087f5b; padding-bottom: 18px; }
        h1 { margin: 0; font-size: 28px; }
        .muted { color: #64716c; }
        .reference { margin: 26px 0; padding: 15px; border: 1px solid #c7d5cb; background: #f4f8f1; font-size: 17px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #d8e0da; text-align: left; }
        th { color: #64716c; font-size: 10px; text-transform: uppercase; }
        .total { margin-top: 25px; text-align: right; font-size: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header"><h1>{{ $invoice->business->name }}</h1><p class="muted">INVOICE {{ $invoice->mpesa_reference }}</p></div>
    <p><strong>Bill to:</strong> {{ $invoice->customer->name }}<br>{{ $invoice->customer->phone }}<br>{{ $invoice->customer->email }}</p>
    <table><thead><tr><th>Description</th><th>Due date</th><th>Status</th></tr></thead><tbody><tr><td>{{ $invoice->description }}</td><td>{{ $invoice->due_date?->format('d M Y') ?? 'On receipt' }}</td><td>{{ ucfirst($invoice->status) }}</td></tr></tbody></table>
    <p class="total">Total: KES {{ number_format((float) $invoice->amount, 2) }}</p>
    <div class="reference">M-Pesa Paybill account reference: {{ $invoice->mpesa_reference }}</div>
    <p class="muted">Please enter the reference above in the account number field when paying.</p>
</body>
</html>