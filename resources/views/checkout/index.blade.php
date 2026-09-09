@extends('layouts.auth')
@section('title', 'Checkout')
@section('content')
<section class="auth-card checkout-card" aria-labelledby="checkout-title">
    <p class="auth-eyebrow">Secure checkout</p>
    <h1 id="checkout-title">Complete your order</h1>
    @if ($addresses->isEmpty())
        <p class="auth-copy">Add a delivery address before placing your order.</p>
        <p class="auth-footer"><a href="{{ route('addresses.index') }}">Manage addresses</a></p>
    @else
        @include('auth.partials.messages')
        <form method="POST" action="{{ route('checkout.store') }}" class="auth-form" id="checkout-form">
            @csrf
            <label for="address_id">Delivery address</label>
            <select id="address_id" name="address_id" required>
                <option value="">Choose an address</option>
                @foreach ($addresses as $address)
                    <option value="{{ $address->id }}" @selected($address->is_default)>{{ $address->full_name }} - {{ $address->city }}, {{ $address->state }} ({{ $address->phone }})</option>
                @endforeach
            </select>
            <p class="auth-links"><a href="{{ route('addresses.index') }}">Add or manage addresses</a></p>
            <label for="payment_method">Payment method</label>
            <select id="payment_method" name="payment_method" required><option value="cod">Cash on Delivery</option><option value="online">Online Payment</option></select>
            <div id="checkout-items" class="checkout-items"></div>
            <div class="checkout-summary" id="checkout-summary" hidden>
                <div><span>Subtotal</span><strong id="checkout-subtotal">Rs 0.00</strong></div>
                <div><span>Shipping</span><strong id="checkout-shipping">Rs 0.00</strong></div>
                <div><span>Estimated delivery</span><strong id="checkout-estimate">-</strong></div>
                <div><span>Total</span><strong id="checkout-total">Rs 0.00</strong></div>
            </div>
            <input type="hidden" name="cart" id="checkout-cart">
            <p class="auth-hint">Final prices are checked securely on the server before the order is created.</p>
            <button class="auth-button" type="submit">Place order</button>
        </form>
    @endif
</section>
@endsection
@push('scripts')
<script>
    const cart = JSON.parse(localStorage.getItem('seeds_bazar_cart') || '[]').map(item => ({ id: Number(item.id), quantity: Number(item.quantity) }));
    const csrf = document.querySelector('input[name="_token"]')?.value;
    const address = document.getElementById('address_id');
    const quoteUrl = @json(route('checkout.quote'));
    document.getElementById('checkout-cart')?.setAttribute('value', JSON.stringify(cart));
    const items = document.getElementById('checkout-items');
    if (items) items.innerHTML = cart.length ? `<strong>${cart.reduce((total, item) => total + item.quantity, 0)} item(s) in your order</strong>` : '<p>Your cart is empty.</p>';
    async function refreshQuote() {
        const summary = document.getElementById('checkout-summary');
        if (!summary || !cart.length || !address?.value) return;
        const response = await fetch(quoteUrl, {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify({address_id: address.value, cart})});
        if (!response.ok) { summary.hidden = true; return; }
        const quote = await response.json();
        summary.hidden = false;
        document.getElementById('checkout-subtotal').textContent = `Rs ${Number(quote.subtotal).toFixed(2)}`;
        document.getElementById('checkout-shipping').textContent = Number(quote.shipping) === 0 ? 'FREE' : `Rs ${Number(quote.shipping).toFixed(2)}`;
        document.getElementById('checkout-estimate').textContent = quote.estimate;
        document.getElementById('checkout-total').textContent = `Rs ${Number(quote.total).toFixed(2)}`;
    }
    address?.addEventListener('change', refreshQuote);
    refreshQuote();
    document.getElementById('checkout-form')?.addEventListener('submit', (event) => {
        if (!cart.length) { event.preventDefault(); alert('Your cart is empty.'); }
    });
</script>
@endpush