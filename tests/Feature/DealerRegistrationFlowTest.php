<?php

namespace Tests\Feature;

use App\Models\Dealer;
use App\Models\DealerGroup;
use App\Models\DealerEmailVerification;
use App\Services\DealerEmailVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DealerRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dealer_can_register_and_get_verification_code()
    {
        Mail::fake();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $response = $this->post('/kayit', [
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'newdealer@example.com',
            'phone' => '05551234567',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İstanbul',
            'district' => 'Kadıköy',
            'address' => 'Adres satırı 1',
            'kvkk_consent' => 'on',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('dealer.verify.show'));

        $dealer = Dealer::where('email', 'newdealer@example.com')->firstOrFail();

        $this->assertEquals($group->id, $dealer->dealer_group_id);
        $this->assertEquals('pending', $dealer->status);
        $this->assertNull($dealer->email_verified_at);

        $verification = DealerEmailVerification::where('dealer_id', $dealer->id)->first();
        $this->assertNotNull($verification);
        $this->assertNull($verification->verified_at);
    }

    #[Test]
    public function dealer_cannot_login_before_email_and_admin_approval()
    {
        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Flow Test Firma',
            'contact_name' => 'Flow Test Kullanıcı',
            'email' => 'pending@example.com',
            'phone' => '05550000000',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'tax_type' => 'tax',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'email_verified_at' => null,
            'status' => 'pending',
        ]);

        $response = $this->from(route('dealer.login'))->post(route('dealer.login.submit'), [
            'email' => $dealer->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dealer.login'));
        $response->assertSessionHas('needs_email_verification', true);

        // E-posta doğrulandı ama admin henüz onaylamadı
        $dealer->forceFill(['email_verified_at' => now(), 'status' => 'pending'])->save();

        $response = $this->from(route('dealer.login'))->post(route('dealer.login.submit'), [
            'email' => $dealer->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dealer.login'));
        $response->assertSessionHas('admin_pending', true);
    }

    #[Test]
    public function dealer_can_verify_email_with_correct_code()
    {
        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Doğrulama Test Firma',
            'contact_name' => 'Doğrulama Kullanıcı',
            'email' => 'verify-success@example.com',
            'phone' => '05559998877',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'tax_type' => 'tax',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'email_verified_at' => null,
            'status' => 'pending',
        ]);

        $code = '123456';
        $hash = hash_hmac('sha256', $code, (string) config('app.key'));

        DealerEmailVerification::create([
            'dealer_id' => $dealer->id,
            'code_hash' => $hash,
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
            'send_count' => 1,
            'attempts' => 0,
            'verified_at' => null,
        ]);

        $response = $this->withSession([
            'dealer_verification_id' => $dealer->id,
        ])->post(route('dealer.verify.submit'), [
            'code' => $code,
        ]);

        $response->assertRedirect(route('dealer.login'));
        $response->assertSessionHas('success');

        $dealer->refresh();
        $verification = DealerEmailVerification::where('dealer_id', $dealer->id)->firstOrFail();

        $this->assertNotNull($dealer->email_verified_at);
        $this->assertNotNull($verification->verified_at);
    }

    #[Test]
    public function dealer_cannot_verify_with_incorrect_code()
    {
        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Yanlış Kod Firma',
            'contact_name' => 'Yanlış Kod Kullanıcı',
            'email' => 'wrong-code@example.com',
            'phone' => '05551112233',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'tax_type' => 'tax',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'email_verified_at' => null,
            'status' => 'pending',
        ]);

        $correctCode = '123456';
        $hash = hash_hmac('sha256', $correctCode, (string) config('app.key'));

        $verification = DealerEmailVerification::create([
            'dealer_id' => $dealer->id,
            'code_hash' => $hash,
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
            'send_count' => 1,
            'attempts' => 0,
            'verified_at' => null,
        ]);

        $response = $this->withSession([
            'dealer_verification_id' => $dealer->id,
        ])->from(route('dealer.verify.submit'))->post(route('dealer.verify.submit'), [
            'code' => '999999',
        ]);

        $response->assertSessionHasErrors(['code'], null, 'verify');

        $dealer->refresh();
        $verification->refresh();

        $this->assertNull($dealer->email_verified_at);
        $this->assertNull($verification->verified_at);
        $this->assertEquals(1, $verification->attempts);
    }

    #[Test]
    public function dealer_cannot_verify_with_expired_code()
    {
        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Süresi Dolmuş Firma',
            'contact_name' => 'Süresi Dolmuş Kullanıcı',
            'email' => 'expired-code@example.com',
            'phone' => '05554443322',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'tax_type' => 'tax',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'email_verified_at' => null,
            'status' => 'pending',
        ]);

        $code = '123456';
        $hash = hash_hmac('sha256', $code, (string) config('app.key'));

        DealerEmailVerification::create([
            'dealer_id' => $dealer->id,
            'code_hash' => $hash,
            'expires_at' => now()->subMinutes(1),
            'last_sent_at' => now()->subMinutes(2),
            'send_count' => 1,
            'attempts' => 0,
            'verified_at' => null,
        ]);

        $response = $this->withSession([
            'dealer_verification_id' => $dealer->id,
        ])->from(route('dealer.verify.submit'))->post(route('dealer.verify.submit'), [
            'code' => $code,
        ]);

        $response->assertSessionHasErrors(['code'], null, 'verify');

        $dealer->refresh();
        $verification = DealerEmailVerification::where('dealer_id', $dealer->id)->firstOrFail();

        $this->assertNull($dealer->email_verified_at);
        $this->assertNull($verification->verified_at);
    }

    #[Test]
    public function dealer_cannot_verify_when_max_attempts_reached()
    {
        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Limit Firma',
            'contact_name' => 'Limit Kullanıcı',
            'email' => 'limit-code@example.com',
            'phone' => '05553334455',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'tax_type' => 'tax',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'email_verified_at' => null,
            'status' => 'pending',
        ]);

        $code = '123456';
        $hash = hash_hmac('sha256', $code, (string) config('app.key'));

        DealerEmailVerification::create([
            'dealer_id' => $dealer->id,
            'code_hash' => $hash,
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
            'send_count' => 1,
            'attempts' => DealerEmailVerificationService::MAX_VERIFY_ATTEMPTS,
            'verified_at' => null,
        ]);

        $response = $this->withSession([
            'dealer_verification_id' => $dealer->id,
        ])->from(route('dealer.verify.submit'))->post(route('dealer.verify.submit'), [
            'code' => $code,
        ]);

        $response->assertSessionHasErrors(['code'], null, 'verify');

        $dealer->refresh();
        $verification = DealerEmailVerification::where('dealer_id', $dealer->id)->firstOrFail();

        $this->assertNull($dealer->email_verified_at);
        $this->assertNull($verification->verified_at);
        $this->assertEquals(DealerEmailVerificationService::MAX_VERIFY_ATTEMPTS, $verification->attempts);
    }

    #[Test]
    public function dealer_can_resend_verification_code_via_json()
    {
        Mail::fake();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Resend Firma',
            'contact_name' => 'Resend Kullanıcı',
            'email' => 'resend@example.com',
            'phone' => '05557778899',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'tax_type' => 'tax',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'email_verified_at' => null,
            'status' => 'pending',
        ]);

        $response = $this->withSession([
            'dealer_verification_id' => $dealer->id,
        ])->postJson(route('dealer.verify.resend'));

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'cooldown_seconds',
        ]);

        $response->assertJson([
            'cooldown_seconds' => DealerEmailVerificationService::RESEND_COOLDOWN_SECONDS,
        ]);

        Mail::assertSent(\App\Mail\DealerVerificationCodeMail::class);

        $verification = DealerEmailVerification::where('dealer_id', $dealer->id)->firstOrFail();
        $this->assertNotNull($verification->last_sent_at);
        $this->assertGreaterThanOrEqual(1, $verification->send_count);
    }

    #[Test]
    public function verified_and_active_dealer_is_redirected_to_panel_when_already_logged_in()
    {
        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Aktif Firma',
            'contact_name' => 'Aktif Kullanıcı',
            'email' => 'active@example.com',
            'phone' => '05550001122',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'tax_type' => 'tax',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($dealer, 'dealer');

        $response = $this->get(route('dealer.login'));

        $response->assertRedirect(route('panel'));
    }
}

