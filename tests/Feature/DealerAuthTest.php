<?php

namespace Tests\Feature;

use App\Models\Dealer;
use App\Models\DealerEmailVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DealerAuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dealer_registration_creates_verification_and_sends_mail()
    {
        Mail::fake();

        $response = $this->post(route('dealer.register.submit'), [
            'company_name' => 'Test Co',
            'contact_name' => 'John Doe',
            'email' => 'dealer@example.com',
            'phone' => '05551234567',
            'tax_office' => 'Test',
            'tax_number' => '1234567890',
            'city' => 'İSTANBUL',
            'district' => 'BEŞİKTAŞ',
            'address' => 'Test address',
            'kvkk_consent' => '1',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('dealer.verify.show'));

        $this->assertDatabaseHas('dealers', [
            'email' => 'dealer@example.com',
            'status' => 'pending',
        ]);

        $dealer = Dealer::where('email', 'dealer@example.com')->firstOrFail();
        $this->assertNotNull(session('dealer_verification_id'));
        $this->assertEquals($dealer->id, session('dealer_verification_id'));

        $this->assertDatabaseHas('dealer_email_verifications', [
            'dealer_id' => $dealer->id,
        ]);
    }

    #[Test]
    public function dealer_can_verify_email_and_then_login_after_admin_approval()
    {
        // prepare dealer
        $dealer = Dealer::create([
            'company_name' => 'Test Co',
            'contact_name' => 'John Doe',
            'email' => 'verifyme@example.com',
            'phone' => '05551234567',
            'tax_office' => 'Test',
            'tax_number' => '1234567890',
            'city' => 'İSTANBUL',
            'district' => 'BEŞİKTAŞ',
            'address' => 'Addr',
            'kvkk_consent' => true,
            'password' => Hash::make('secret123'),
            'status' => 'pending',
        ]);

        // create verification record with known code 123456
        $code = '123456';
        $hash = hash_hmac('sha256', $code, (string) config('app.key'));
        DealerEmailVerification::create([
            'dealer_id' => $dealer->id,
            'code_hash' => $hash,
            'expires_at' => now()->addMinutes(10),
            'send_count' => 1,
            'attempts' => 0,
        ]);

        // simulate session
        $this->withSession(['dealer_verification_id' => $dealer->id])
            ->post(route('dealer.verify.submit'), ['code' => $code])
            ->assertRedirect(route('dealer.verify.show'));

        $dealer->refresh();
        $this->assertNotNull($dealer->email_verified_at);

        // admin approves
        $dealer->update(['status' => 'active']);

        // attempt login
        $this->post(route('dealer.login.submit'), [
            'email' => $dealer->email,
            'password' => 'secret123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($dealer, 'dealer');
    }

    #[Test]
    public function dealer_login_before_verification_requires_verification()
    {
        $dealer = Dealer::create([
            'company_name' => 'Test Co',
            'contact_name' => 'John Doe',
            'email' => 'novrfy@example.com',
            'phone' => '05551234567',
            'tax_office' => 'Test',
            'tax_number' => '1234567890',
            'city' => 'İSTANBUL',
            'district' => 'BEŞİKTAŞ',
            'address' => 'Addr',
            'kvkk_consent' => true,
            'password' => Hash::make('secret123'),
            'status' => 'pending',
        ]);

        $response = $this->from(route('dealer.login'))->post(route('dealer.login.submit'), [
            'email' => $dealer->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dealer.login'));
        $this->assertTrue(session()->has('dealer_verification_id'));
        $this->assertGuest('dealer');
    }

    #[Test]
    public function dealer_with_inactive_status_cannot_login()
    {
        $dealer = Dealer::create([
            'company_name' => 'Test Co',
            'contact_name' => 'John Doe',
            'email' => 'inactive@example.com',
            'phone' => '05551234567',
            'tax_office' => 'Test',
            'tax_number' => '1234567890',
            'city' => 'İSTANBUL',
            'district' => 'BEŞİKTAŞ',
            'address' => 'Addr',
            'kvkk_consent' => true,
            'password' => Hash::make('secret123'),
            'status' => 'passive',
            'email_verified_at' => now(),
        ]);

        $response = $this->from(route('dealer.login'))->post(route('dealer.login.submit'), [
            'email' => $dealer->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dealer.login'));
        $this->assertGuest('dealer');
    }

    #[Test]
    public function dealer_logout_works()
    {
        $dealer = Dealer::create([
            'company_name' => 'Test Co',
            'contact_name' => 'John Doe',
            'email' => 'logout@example.com',
            'phone' => '05551234567',
            'tax_office' => 'Test',
            'tax_number' => '1234567890',
            'city' => 'İSTANBUL',
            'district' => 'BEŞİKTAŞ',
            'address' => 'Addr',
            'kvkk_consent' => true,
            'password' => Hash::make('secret123'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->be($dealer, 'dealer');
        $this->post(route('dealer.logout'))->assertRedirect(route('dealer.login'));
        $this->assertGuest('dealer');
    }
}

