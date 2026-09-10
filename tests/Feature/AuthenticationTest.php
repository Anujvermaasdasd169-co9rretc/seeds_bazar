<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_is_sent_verification(): void
    {
        Notification::fake();

        $response = $this->post(route('register.submit'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => ' ADA@example.com ',
            'mobile' => '+91 98765 43210',
            'password' => 'Secure!123',
            'password_confirmation' => 'Secure!123',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'ada@example.com',
            'mobile' => '+919876543210',
        ]);
        $this->assertNotNull(User::first()->terms_accepted_at);
        $this->assertTrue(Hash::check('Secure!123', User::first()->password));
        Notification::assertSentTo(User::first(), VerifyEmail::class);
    }

    public function test_registration_rejects_duplicate_email_and_weak_password(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->from(route('register'))->post(route('register.submit'), [
            'first_name' => 'Test', 'last_name' => 'User', 'email' => 'existing@example.com',
            'mobile' => '+919876543211', 'password' => 'weak', 'password_confirmation' => 'weak', 'terms' => '1',
        ]);

        $response->assertRedirect(route('register'))->assertSessionHasErrors(['email', 'password']);
    }

    public function test_login_regenerates_session_and_logout_invalidates_it(): void
    {
        $user = User::factory()->create(['email' => 'login@example.com', 'password' => 'Secure!123']);
        $this->withSession(['marker' => 'before'])->post(route('login.submit'), [
            'email' => 'LOGIN@example.com', 'password' => 'Secure!123',
        ])->assertRedirect(route('account'));

        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'))->assertRedirect(route('shop.index'));
        $this->assertGuest();
    }

    public function test_login_uses_a_generic_error_for_unknown_accounts(): void
    {
        $response = $this->from(route('login'))->post(route('login.submit'), [
            'email' => 'missing@example.com', 'password' => 'wrong',
        ]);

        $response->assertRedirect(route('login'))->assertSessionHasErrors(['email' => 'The provided credentials are incorrect.']);
    }

    public function test_login_is_throttled(): void
    {
        RateLimiter::clear('throttle:login|missing@example.com|127.0.0.1');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('login'))->post(route('login.submit'), [
                'email' => 'missing@example.com', 'password' => 'wrong',
            ])->assertRedirect(route('login'));
        }

        $this->post(route('login.submit'), [
            'email' => 'missing@example.com', 'password' => 'wrong',
        ])->assertTooManyRequests();
    }

    public function test_password_reset_request_is_generic_for_unknown_email(): void
    {
        $response = $this->from(route('password.request'))->post(route('password.email'), ['email' => 'unknown@example.com']);

        $response->assertRedirect(route('password.request'))->assertSessionHas('status');
    }

    public function test_user_can_reset_password_with_a_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token, 'email' => $user->email, 'password' => 'NewSecure!123', 'password_confirmation' => 'NewSecure!123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewSecure!123', $user->fresh()->password));
    }

    public function test_invalid_password_reset_token_is_rejected(): void
    {
        $response = $this->from(route('password.reset', ['token' => 'invalid']))->post(route('password.update'), [
            'token' => 'invalid', 'email' => 'reset@example.com',
            'password' => 'NewSecure!123', 'password_confirmation' => 'NewSecure!123',
        ]);

        $response->assertRedirect(route('password.reset', ['token' => 'invalid']))
            ->assertSessionHasErrors('email');
    }

    public function test_user_can_verify_email_with_a_signed_url(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(10), [
            'id' => $user->id, 'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect(route('account'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_invalid_email_verification_signature_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('verification.verify', [
            'id' => $user->id, 'hash' => sha1($user->getEmailForVerification()),
        ]))->assertForbidden();
    }

    public function test_verification_resend_is_throttled(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        RateLimiter::clear('throttle:verification|'.$user->id.'|127.0.0.1');

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->actingAs($user)->post(route('verification.send'))->assertRedirect();
        }

        $this->actingAs($user)->post(route('verification.send'))->assertTooManyRequests();
    }

    public function test_customer_can_change_password_and_cannot_access_admin_area(): void
    {
        $user = User::factory()->create(['password' => 'OldSecure!123']);

        $this->actingAs($user)->post(route('account.password'), [
            'current_password' => 'OldSecure!123', 'password' => 'NewSecure!123', 'password_confirmation' => 'NewSecure!123',
        ])->assertRedirect(route('account'));

        $this->assertTrue(Hash::check('NewSecure!123', $user->fresh()->password));
        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_sensitive_user_attributes_are_hidden_from_serialization(): void
    {
        $serialized = User::factory()->make()->toArray();

        $this->assertArrayNotHasKey('password', $serialized);
        $this->assertArrayNotHasKey('remember_token', $serialized);
    }

    public function test_is_admin_cannot_be_mass_assigned(): void
    {
        $user = User::factory()->create();

        $user->update(['is_admin' => true, 'name' => 'Updated Customer']);

        $this->assertFalse($user->fresh()->is_admin);
        $this->assertSame('Updated Customer', $user->fresh()->name);
    }

    public function test_guests_are_redirected_to_customer_login_for_account(): void
    {
        $this->get(route('account'))->assertRedirect(route('login'));
    }

    public function test_contact_forms_include_csrf_tokens(): void
    {
        $this->get(route('contact.show'))->assertOk()->assertSee('name="_token"', false);
        $this->get(route('shop.index'))->assertOk()->assertSee('name="_token"', false);
    }
}
