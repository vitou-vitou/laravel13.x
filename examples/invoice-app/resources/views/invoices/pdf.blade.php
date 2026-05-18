<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Invoice {{ $invoice->number }}</h1>
    <p>
        <strong>Bill To:</strong><br>
        {{ $invoice->customer->name }}<br>
        {{ $invoice->customer->address }}
    </p>
    <p>Issued: {{ $invoice->issued_on->toDateString() }} | Due: {{ $invoice->due_on->toDateString() }}</p>
    <table>
        <thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
        <tbody>
        @foreach ($invoice->items as $i)
            <tr>
                <td>{{ $i->description }}</td>
                <td>{{ $i->quantity }}</td>
                <td class="right">{{ number_format($i->unit_price, 2) }}</td>
                <td class="right">{{ number_format($i->line_total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot><tr><td colspan="3" class="right"><strong>Total</strong></td><td class="right"><strong>{{ number_format($invoice->total, 2) }}</strong></td></tr></tfoot>
    </table>
</body>
</html>
