@props([
    'panel' => null,
])

@php
    $user = auth()->user();
    $panel = $panel ?: session('account_panel');
    $addresses = $user ? $user->addresses()->latest()->get() : collect();
    $editing = $user ? $user->addresses()->find((int) request('edit')) : null;
    $guestPanes = ['login', 'register', 'forgot'];
    $authPanes = ['profile', 'password', 'addresses', 'verify'];
    $defaultPanel = $user ? 'profile' : 'login';
    $activePanel = in_array($panel, $user ? $authPanes : $guestPanes, true) ? $panel : $defaultPanel;
@endphp

<div class="account-overlay" id="account-overlay" hidden></div>
<div class="account-hub" id="account-hub" role="dialog" aria-modal="true" aria-labelledby="account-hub-title" hidden
     data-active-panel="{{ $activePanel }}"
     data-route-login="{{ route('login') }}"
     data-route-register="{{ route('register') }}"
     data-route-forgot="{{ route('password.request') }}"
     data-route-profile="{{ route('account') }}"
     data-route-password="{{ route('account') }}#password"
     data-route-addresses="{{ route('addresses.index') }}"
     data-route-verify="{{ route('verification.notice') }}"
     data-route-home="{{ url('/') }}">
    <button type="button" class="account-hub__close" id="account-hub-close" aria-label="Close account">&times;</button>

    <div class="account-hub__side">
        <p class="account-hub__eyebrow">Seed Planta</p>
        <h2 id="account-hub-title">Your garden, your account</h2>
        <p>Save addresses, track orders, and check out in a few taps. Stay on this page while you sign in.</p>
        <ul class="account-hub__perks">
            <li>Germination guidance with every pack</li>
            <li>COD and secure online payment</li>
            <li>Free shipping over ₹{{ number_format((float) \App\Models\Setting::get('free_shipping_threshold', (string) config('seeds_bazar.shipping.free_threshold')), 0) }}</li>
        </ul>
    </div>

    <div class="account-hub__main">
        <div class="account-hub__tabs" role="tablist" aria-label="Account">
            @guest
                <a href="{{ route('login') }}" class="account-hub__tab js-account-link" data-account-panel="login" role="tab">Sign in</a>
                <a href="{{ route('register') }}" class="account-hub__tab js-account-link" data-account-panel="register" role="tab">Create account</a>
                <a href="{{ route('password.request') }}" class="account-hub__tab js-account-link" data-account-panel="forgot" role="tab">Forgot</a>
            @else
                <a href="{{ route('account') }}" class="account-hub__tab js-account-link" data-account-panel="profile" role="tab">Profile</a>
                <a href="{{ route('account') }}#password" class="account-hub__tab js-account-link" data-account-panel="password" role="tab">Password</a>
                <a href="{{ route('addresses.index') }}" class="account-hub__tab js-account-link" data-account-panel="addresses" role="tab">Addresses</a>
                @unless ($user->hasVerifiedEmail())
                    <a href="{{ route('verification.notice') }}" class="account-hub__tab js-account-link" data-account-panel="verify" role="tab">Verify</a>
                @endunless
            @endguest
        </div>

        @include('auth.partials.messages')

        <div class="account-hub__viewport">
            <div class="account-hub__track" id="account-hub-track">
                @guest
                    <section class="account-hub__pane" data-pane="login">
                        <p class="auth-eyebrow">Welcome back</p>
                        <h3>Sign in to buy faster</h3>
                        <form method="POST" action="{{ route('login.submit') }}" class="auth-form">
                            @csrf
                            @php
                                $intended = url()->previous();
                                $appUrl = rtrim((string) config('app.url'), '/');
                            @endphp
                            @if ($intended && str_starts_with($intended, $appUrl))
                                <input type="hidden" name="intended" value="{{ $intended }}">
                            @endif
                            <label for="hub-login-email">Email address</label>
                            <input id="hub-login-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                            @error('email')<p class="field-error">{{ $message }}</p>@enderror
                            <label for="hub-login-password">Password</label>
                            <input id="hub-login-password" name="password" type="password" autocomplete="current-password" required>
                            <label class="auth-check"><input type="checkbox" name="remember" value="1"> Remember me</label>
                            <button class="auth-button" type="submit">Sign in &amp; continue</button>
                        </form>
                    </section>

                    <section class="account-hub__pane" data-pane="register">
                        <p class="auth-eyebrow">Join the garden</p>
                        <h3>Create your account</h3>
                        <form method="POST" action="{{ route('register.submit') }}" class="auth-form">
                            @csrf
                            <div class="auth-grid">
                                <div><label for="hub-first-name">First name</label><input id="hub-first-name" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" required></div>
                                <div><label for="hub-last-name">Last name</label><input id="hub-last-name" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" required></div>
                            </div>
                            <label for="hub-register-email">Email address</label>
                            <input id="hub-register-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                            <label for="hub-register-mobile">Mobile number</label>
                            <input id="hub-register-mobile" name="mobile" type="tel" value="{{ old('mobile') }}" autocomplete="tel" required>
                            <label for="hub-register-password">Password</label>
                            <input id="hub-register-password" name="password" type="password" autocomplete="new-password" required>
                            <p class="auth-hint">8+ characters with upper, lower, a number, and a symbol.</p>
                            <label for="hub-register-password-confirmation">Confirm password</label>
                            <input id="hub-register-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                            <label class="auth-check"><input type="checkbox" name="terms" value="1" required> I accept the <a href="{{ route('policies.terms') }}" target="_blank">Terms</a>.</label>
                            <button class="auth-button" type="submit">Create account</button>
                        </form>
                    </section>

                    <section class="account-hub__pane" data-pane="forgot">
                        <p class="auth-eyebrow">Account recovery</p>
                        <h3>Forgot your password?</h3>
                        <p class="auth-copy">We will email a reset link if this address has an account.</p>
                        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                            @csrf
                            <label for="hub-forgot-email">Email address</label>
                            <input id="hub-forgot-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                            <button class="auth-button" type="submit">Send reset link</button>
                        </form>
                    </section>
                @else
                    <section class="account-hub__pane" data-pane="profile">
                        <p class="auth-eyebrow">Hello, {{ $user->first_name ?: $user->name }}</p>
                        <h3>Your profile</h3>
                        @unless ($user->hasVerifiedEmail())
                            <div class="auth-alert auth-alert--notice">Verify your email to keep order updates flowing. <a href="{{ route('verification.notice') }}" class="js-account-link" data-account-panel="verify">Verify now</a></div>
                        @endunless
                        <form method="POST" action="{{ route('account.profile') }}" class="auth-form">
                            @csrf @method('PUT')
                            <div class="auth-grid">
                                <label>First name<input name="first_name" value="{{ old('first_name', $user->first_name) }}" required></label>
                                <label>Last name<input name="last_name" value="{{ old('last_name', $user->last_name) }}" required></label>
                            </div>
                            <label>Email<input name="email" type="email" value="{{ old('email', $user->email) }}" required></label>
                            <label>Mobile<input name="mobile" type="tel" value="{{ old('mobile', $user->mobile) }}" required></label>
                            <button class="auth-button" type="submit">Save profile</button>
                        </form>
                        <p class="auth-links"><a href="{{ route('orders.index') }}">View orders</a></p>
                        <form method="POST" action="{{ route('logout') }}" class="auth-inline-form">@csrf<button type="submit" class="auth-text-button">Sign out</button></form>
                    </section>

                    <section class="account-hub__pane" data-pane="password">
                        <p class="auth-eyebrow">Security</p>
                        <h3>Change password</h3>
                        <form method="POST" action="{{ route('account.password') }}" class="auth-form">
                            @csrf
                            <label for="hub-current-password">Current password</label>
                            <input id="hub-current-password" name="current_password" type="password" autocomplete="current-password" required>
                            <label for="hub-new-password">New password</label>
                            <input id="hub-new-password" name="password" type="password" autocomplete="new-password" required>
                            <label for="hub-new-password-confirmation">Confirm new password</label>
                            <input id="hub-new-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                            <button class="auth-button" type="submit">Update password</button>
                        </form>
                    </section>

                    <section class="account-hub__pane" data-pane="addresses">
                        <p class="auth-eyebrow">Delivery</p>
                        <h3>Your addresses</h3>
                        <div class="hub-address-list">
                            @forelse ($addresses as $address)
                                <article class="hub-address-card">
                                    <div>
                                        <strong>{{ $address->full_name }}</strong>
                                        @if ($address->is_default)<span class="address-default">Default</span>@endif
                                        <p>{{ $address->phone }}<br>{{ $address->address_line_1 }}{{ $address->address_line_2 ? ', '.$address->address_line_2 : '' }}<br>{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
                                    </div>
                                    <div class="hub-address-card__actions">
                                        <a class="auth-text-button js-account-link" data-account-panel="addresses" href="{{ route('addresses.index', ['edit' => $address->id]) }}">Edit</a>
                                        @unless ($address->is_default)
                                            <form method="POST" action="{{ route('addresses.default', $address) }}">@csrf @method('PATCH')<button class="auth-text-button" type="submit">Default</button></form>
                                        @endunless
                                        <form method="POST" action="{{ route('addresses.destroy', $address) }}">@csrf @method('DELETE')<button class="auth-text-button" type="submit">Remove</button></form>
                                    </div>
                                </article>
                            @empty
                                <p class="reviews-empty">Add a delivery address so checkout takes one tap.</p>
                            @endforelse
                        </div>
                        <form method="POST" action="{{ $editing ? route('addresses.update', $editing) : route('addresses.store') }}" class="auth-form">
                            @csrf @if ($editing) @method('PUT') @endif
                            <div class="auth-grid">
                                <label>Full name<input name="full_name" value="{{ old('full_name', $editing?->full_name ?: $user->name) }}" required></label>
                                <label>Phone<input name="phone" type="tel" value="{{ old('phone', $editing?->phone ?: $user->mobile) }}" required></label>
                            </div>
                            <label>Address line 1<input name="address_line_1" value="{{ old('address_line_1', $editing?->address_line_1) }}" required></label>
                            <div class="auth-grid">
                                <label>Address line 2<input name="address_line_2" value="{{ old('address_line_2', $editing?->address_line_2) }}"></label>
                                <label>Landmark<input name="landmark" value="{{ old('landmark', $editing?->landmark) }}"></label>
                            </div>
                            <div class="auth-grid">
                                <label>City<input name="city" value="{{ old('city', $editing?->city) }}" required></label>
                                <label>State<input name="state" value="{{ old('state', $editing?->state) }}" required></label>
                            </div>
                            <div class="auth-grid">
                                <label>Postal code<input name="postal_code" inputmode="numeric" value="{{ old('postal_code', $editing?->postal_code) }}" required></label>
                                <label>Country<input name="country" value="{{ old('country', $editing?->country ?: 'India') }}" required></label>
                            </div>
                            <label class="auth-check"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $editing?->is_default))> Default address</label>
                            <button class="auth-button" type="submit">{{ $editing ? 'Update address' : 'Save address' }}</button>
                        </form>
                    </section>

                    <section class="account-hub__pane" data-pane="verify">
                        <p class="auth-eyebrow">One last step</p>
                        <h3>Verify your email</h3>
                        <p class="auth-copy">We sent a link to {{ $user->email }}. You can keep shopping while you verify.</p>
                        <form method="POST" action="{{ route('verification.send') }}" class="auth-form">@csrf<button class="auth-button" type="submit">Send another link</button></form>
                    </section>
                @endauth
            </div>
        </div>
    </div>
</div>
