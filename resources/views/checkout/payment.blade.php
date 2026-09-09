@extends('layouts.auth')
@section('title', 'Online payment')
@section('content')
<section class="auth-card" aria-labelledby="payment-title">
    <p class="auth-eyebrow">Secure online payment</p>
    <h1 id="payment-title">Pay for order {{ $order->order_number }}</h1>
    @if ($errors->any())
        <div class="auth-alert auth-alert--error" role="alert">{{ $errors->first('payment') ?: $errors->first() }}</div>
    @endif
    @if (! $razorpayKeyId)
        <div class="auth-alert auth-alert--notice">Online payments are not configured for this local environment. Choose Cash on Delivery at checkout.</div>
    @else
        <p class="auth-copy">Amount payable: <strong>Rs {{ number_format($order->total, 2) }}</strong></p>
        <button class="auth-button" id="pay-button" type="button">Pay securely</button>
        <form method="POST" action="{{ route('payments.verify', $order) }}" id="payment-verification-form" hidden>
            @csrf
            <input name="razorpay_payment_id" id="razorpay_payment_id">
            <input name="razorpay_signature" id="razorpay_signature">
        </form>
        <form method="POST" action="{{ route('payments.fail', $order) }}" class="auth-inline-form">
            @csrf
            <button class="auth-text-button" type="submit">Cancel payment</button>
        </form>
    @endif
    <p class="auth-footer"><a href="{{ route('orders.show', $order) }}">Back to order</a></p>
</section>
@endsection
@if ($razorpayKeyId)
    @push('scripts')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            document.getElementById('pay-button')?.addEventListener('click', function () {
                const options = {
                    key: @json($razorpayKeyId),
                    amount: {{ (int) round((float) $order->total * 100) }},
                    currency: 'INR',
                    name: @json(config('app.name')),
                    order_id: @json($order->gateway_order_id),
                    handler: function (response) {
                        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                        document.getElementById('razorpay_signature').value = response.razorpay_signature;
                        document.getElementById('payment-verification-form').hidden = false;
                        document.getElementById('payment-verification-form').submit();
                    },
                    modal: { ondismiss: function () { } }
                };
                new Razorpay(options).open();
            });
        </script>
    @endpush
@endif