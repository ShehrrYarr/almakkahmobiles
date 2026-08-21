<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt</title>
    <style>
        html, body { background: #fff; margin: 0; padding: 0; width: 100%; }
        .receipt { width: 72mm; margin: 16px auto; font-family: Arial, sans-serif; font-size: 12px; color: #000; }
        @media print {
            html, body { margin: 0 !important; padding: 0 !important; width: 80mm !important; }
            .receipt { width: 72mm !important; margin: 0 auto !important; }
            .no-print { display: none !important; }
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        td, th { padding: 2px 0; }
        hr { border: none; border-top: 1px dashed #000; margin: 6px 0; }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="center bold" style="font-size:15px;">Al Makkah Mobiles</div>
        <div class="center">Mobile Sale Receipt</div>
        <hr>
        <div>Invoice #: {{ $sale->id }}</div>
        <div>Date: {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y, H:i') }}</div>
        <div>Cashier: {{ $sale->user->name ?? '-' }}</div>
        @if($sale->vendor)
        <div>Vendor: {{ $sale->vendor->name }}</div>
        @elseif($sale->customer_name)
        <div>Customer: {{ $sale->customer_name }}</div>
        @if($sale->customer_mobile)<div>Mobile: {{ $sale->customer_mobile }}</div>@endif
        @endif
        @if(!empty($sale->comment))
        <div>Note: {{ $sale->comment }}</div>
        @endif
        <hr>
        <table>
            <thead>
                <tr>
                    <th style="text-align:left;">Item</th>
                    <th style="text-align:right;">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td style="text-align:left;">
                        {{ $item->unit->mobile->name ?? '-' }}<br>
                        <span style="font-size:10px;color:#555;">IMEI: {{ $item->unit->imei ?? '-' }}</span>
                    </td>
                    <td style="text-align:right;">{{ number_format($item->price, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <hr>
        @php
            $netTotal = (float) $sale->total_amount;
            $discount = (float) ($sale->discount_amount ?? 0);
            $isVendorSale = !empty($sale->vendor);
            $paid = (float) ($sale->pay_amount ?? 0);
            $remaining = $isVendorSale ? max($netTotal - $paid, 0) : 0;
        @endphp
        <table>
            @if($discount > 0)
            <tr><td>Before Discount</td><td style="text-align:right;">Rs. {{ number_format($netTotal + $discount, 0) }}</td></tr>
            <tr><td>Discount</td><td style="text-align:right;">-Rs. {{ number_format($discount, 0) }}</td></tr>
            @endif
            <tr class="bold"><td>Total</td><td style="text-align:right;">Rs. {{ number_format($netTotal, 0) }}</td></tr>
            @if($sale->payments->isNotEmpty())
            @foreach($sale->payments as $p)
            <tr><td>Paid ({{ ucfirst($p->method) }}{{ $p->bank ? ' - '.$p->bank->name : '' }})</td><td style="text-align:right;">Rs. {{ number_format($p->amount, 0) }}</td></tr>
            @endforeach
            @endif
            @if($isVendorSale && $remaining > 0)
            <tr class="bold"><td>Remaining (on vendor account)</td><td style="text-align:right;">Rs. {{ number_format($remaining, 0) }}</td></tr>
            @endif
        </table>
        <hr>
        <div class="center">Thank you!</div>
    </div>
    <div class="no-print center" style="margin-top:12px;">
        <button onclick="window.print()">Print</button>
    </div>
</body>
</html>
