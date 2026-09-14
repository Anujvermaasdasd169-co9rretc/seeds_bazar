<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject ?? 'Your Seed Planta order' }}</title>
</head>
<body style="margin:0;padding:24px;background:#f6f4ef;font-family:Arial,Helvetica,sans-serif;color:#1f2933;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;background:#ffffff;border-radius:12px;padding:28px;">
                    <tr>
                        <td>
                            <p style="margin:0 0 8px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#6b7280;">Seed Planta</p>
                            <h1 style="margin:0 0 16px;font-size:22px;">Order {{ $order->order_number }}</h1>
                            <p style="margin:0 0 20px;line-height:1.5;">
                                @if ($order->payment_method === 'cod')
                                    We received your Cash on Delivery order and will start packing it shortly.
                                @elseif ($order->payment_status === 'paid')
                                    Payment is confirmed. We will pack and ship your seeds next.
                                @else
                                    We received your order.
                                @endif
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-top:1px solid #eee;border-bottom:1px solid #eee;margin:0 0 20px;">
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td style="padding:10px 0;">{{ $item->product_name }} × {{ $item->quantity }}</td>
                                        <td style="padding:10px 0;" align="right">₹{{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            <p style="margin:0 0 6px;"><strong>Total:</strong> ₹{{ number_format($order->total, 2) }}</p>
                            <p style="margin:0 0 6px;"><strong>Payment:</strong> {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online' }} ({{ ucfirst($order->payment_status) }})</p>
                            <p style="margin:0 0 20px;"><strong>Ship to:</strong> {{ $order->shipping_name }}, {{ $order->shipping_address }}</p>
                            @if ($order->delivery_estimate)
                                <p style="margin:0 0 20px;">Estimated delivery: {{ $order->delivery_estimate }}</p>
                            @endif
                            <p style="margin:0;font-size:13px;color:#6b7280;">If you have a question, reply to this email or contact us from the website.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
