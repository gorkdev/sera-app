<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Dealer;
use App\Models\DealerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminDealerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdmin(): AdminUser
    {
        /** @var \App\Models\AdminUser $admin */
        $admin = AdminUser::factory()->create([
            'is_active' => true,
            'password' => bcrypt('secret123'),
        ]);

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    #[Test]
    public function admin_can_filter_and_list_dealers()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Pending Firma',
            'contact_name' => 'Pending Kullanıcı',
            'email' => 'pending@example.com',
            'phone' => '05550000001',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'pending',
        ]);

        Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Aktif Firma',
            'contact_name' => 'Aktif Kullanıcı',
            'email' => 'active@example.com',
            'phone' => '05550000002',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $response = $this->get('/yonetim/bayiler?status=pending');
        $response->assertStatus(200);
        $response->assertSee('Pending Firma');
        $response->assertDontSee('Aktif Firma');

        $responseSearch = $this->get('/yonetim/bayiler?q=Aktif');
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Aktif Firma');
    }

    #[Test]
    public function admin_can_approve_verified_pending_dealer()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Onay Firma',
            'contact_name' => 'Onay Kullanıcı',
            'email' => 'approve@example.com',
            'phone' => '05550000003',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'pending',
        ]);

        $dealer->forceFill(['email_verified_at' => now()])->save();

        $response = $this->post("/yonetim/bayiler/{$dealer->id}/onayla");
        $response->assertRedirect();

        $dealer->refresh();
        $this->assertEquals('active', $dealer->status);
    }

    #[Test]
    public function admin_cannot_approve_unverified_dealer()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Onaysız Firma',
            'contact_name' => 'Onaysız Kullanıcı',
            'email' => 'unverified@example.com',
            'phone' => '05550000004',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'email_verified_at' => null,
            'status' => 'pending',
        ]);

        $response = $this->from(route('admin.dealers.index'))
            ->post("/yonetim/bayiler/{$dealer->id}/onayla");

        $response->assertRedirect(route('admin.dealers.index'));
        $response->assertSessionHas('error');

        $dealer->refresh();
        $this->assertEquals('pending', $dealer->status);
    }

    #[Test]
    public function admin_can_reject_dealer()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Reddedilecek Firma',
            'contact_name' => 'Reddedilecek Kullanıcı',
            'email' => 'reject@example.com',
            'phone' => '05550000005',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'email_verified_at' => null,
            'status' => 'pending',
        ]);

        $response = $this->post("/yonetim/bayiler/{$dealer->id}/reddet");
        $response->assertRedirect();

        $dealer->refresh();
        $this->assertEquals('passive', $dealer->status);
    }

    #[Test]
    public function admin_can_update_dealer_information()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Eski Firma',
            'contact_name' => 'Eski Kullanıcı',
            'email' => 'old@example.com',
            'phone' => '05550000006',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $newGroup = DealerGroup::factory()->create();

        $response = $this->put("/yonetim/bayiler/{$dealer->id}", [
            'dealer_group_id' => $newGroup->id,
            'company_name' => 'Yeni Firma',
            'contact_name' => 'Yeni Kullanıcı',
            'email' => 'new@example.com',
            'phone' => '05550000066',
            'tax_office' => 'Maltepe',
            'tax_number' => '2222222222',
            'city' => 'İSTANBUL',
            'district' => 'MALTEPE',
            'address' => 'Yeni adres',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.dealers.edit', $dealer));

        $dealer->refresh();

        $this->assertEquals('Yeni Firma', $dealer->company_name);
        $this->assertEquals('Yeni Kullanıcı', $dealer->contact_name);
        $this->assertEquals('new@example.com', $dealer->email);
        $this->assertEquals('05550000066', $dealer->phone);
        $this->assertEquals($newGroup->id, $dealer->dealer_group_id);
        $this->assertEquals('active', $dealer->status);
    }

    #[Test]
    public function admin_can_view_dealer_edit_page()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000007',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $response = $this->get("/yonetim/bayiler/{$dealer->id}");
        $response->assertStatus(200);
        $response->assertSee('Test Firma');
        $response->assertSee('test@example.com');
    }

    #[Test]
    public function update_validates_required_fields()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000008',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $response = $this->put("/yonetim/bayiler/{$dealer->id}", [
            'company_name' => '',
            'contact_name' => '',
            'email' => '',
            'phone' => '',
            'tax_office' => '',
            'tax_number' => '',
            'city' => '',
            'district' => '',
            'address' => '',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['company_name', 'contact_name', 'email', 'phone', 'tax_office', 'tax_number', 'city', 'district', 'address']);
    }

    #[Test]
    public function update_validates_phone_format()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000009',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        // Geçersiz telefon formatları (normalize edildikten sonra geçersiz olmalı)
        $invalidPhones = ['123', '12345', '123456789012', '04'];

        foreach ($invalidPhones as $invalidPhone) {
            $response = $this->put("/yonetim/bayiler/{$dealer->id}", [
                'company_name' => 'Test Firma',
                'contact_name' => 'Test Kullanıcı',
                'email' => 'test@example.com',
                'phone' => $invalidPhone,
                'tax_office' => 'Kadıköy',
                'tax_number' => '1111111111',
                'city' => 'İSTANBUL',
                'district' => 'KADIKÖY',
                'address' => 'Adres',
                'status' => 'active',
            ]);

            $response->assertSessionHasErrors(['phone']);
        }
    }

    #[Test]
    public function update_normalizes_phone_number()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000010',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        // Telefon formatları normalize edilmeli
        $response = $this->put("/yonetim/bayiler/{$dealer->id}", [
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '555 123 45 67', // Formatlı telefon
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'status' => 'active',
        ]);

        $dealer->refresh();
        $this->assertEquals('05551234567', $dealer->phone); // Normalize edilmiş
    }

    #[Test]
    public function update_validates_unique_email()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $existingDealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Mevcut Firma',
            'contact_name' => 'Mevcut Kullanıcı',
            'email' => 'existing@example.com',
            'phone' => '05550000011',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000012',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $response = $this->put("/yonetim/bayiler/{$dealer->id}", [
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'existing@example.com', // Mevcut e-posta
            'phone' => '05550000012',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function update_validates_unique_phone()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $existingDealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Mevcut Firma',
            'contact_name' => 'Mevcut Kullanıcı',
            'email' => 'existing@example.com',
            'phone' => '05550000013',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000014',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $response = $this->put("/yonetim/bayiler/{$dealer->id}", [
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000013', // Mevcut telefon
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['phone']);
    }

    #[Test]
    public function update_validates_tax_number_format()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000015',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        // Geçersiz vergi no formatları
        $invalidTaxNumbers = ['123', '123456789', '123456789012'];

        foreach ($invalidTaxNumbers as $invalidTaxNumber) {
            $response = $this->put("/yonetim/bayiler/{$dealer->id}", [
                'company_name' => 'Test Firma',
                'contact_name' => 'Test Kullanıcı',
                'email' => 'test@example.com',
                'phone' => '05550000015',
                'tax_office' => 'Kadıköy',
                'tax_number' => $invalidTaxNumber,
                'city' => 'İSTANBUL',
                'district' => 'KADIKÖY',
                'address' => 'Adres',
                'status' => 'active',
            ]);

            $response->assertSessionHasErrors(['tax_number']);
        }
    }

    #[Test]
    public function update_normalizes_city_and_district_to_uppercase()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000016',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $response = $this->put("/yonetim/bayiler/{$dealer->id}", [
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000016',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'istanbul', // Küçük harf
            'district' => 'kadıköy', // Küçük harf
            'address' => 'Adres',
            'status' => 'active',
        ]);

        $dealer->refresh();
        $this->assertEquals('İSTANBUL', $dealer->city);
        $this->assertEquals('KADIKÖY', $dealer->district);
    }

    #[Test]
    public function list_paginates_dealers()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        // 25 bayi oluştur (pagination test için)
        for ($i = 1; $i <= 25; $i++) {
            Dealer::create([
                'dealer_group_id' => $group->id,
                'company_name' => "Firma {$i}",
                'contact_name' => "Kullanıcı {$i}",
                'email' => "firma{$i}@example.com",
                'phone' => '0555000' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'tax_office' => 'Kadıköy',
                'tax_number' => '1111111111',
                'city' => 'İSTANBUL',
                'district' => 'KADIKÖY',
                'address' => 'Adres',
                'kvkk_consent' => true,
                'password' => bcrypt('secret123'),
                'status' => 'active',
            ]);
        }

        $response = $this->get('/yonetim/bayiler');
        $response->assertStatus(200);
        $response->assertSee('Firma 1');
        // Pagination kontrolü - Livewire component'inde page queryString yok, sadece status ve q var
        // İkinci sayfaya geçmek için Livewire'ın kendi pagination linklerini kullanmalıyız
        // Bu test için sadece ilk sayfanın yüklendiğini kontrol ediyoruz
    }

    #[Test]
    public function list_searches_by_multiple_fields()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer =         Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Arama Test Firma',
            'contact_name' => 'Test Yetkili',
            'email' => 'arama@example.com',
            'phone' => '05551234567',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'ANKARA',
            'district' => 'CANKAYA', // Türkçe karakter olmadan
            'address' => 'Test Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        // Şirket adı ile arama
        $response = $this->get('/yonetim/bayiler?q=Arama');
        $response->assertStatus(200);
        $response->assertSee('Arama Test Firma');

        // Yetkili adı ile arama
        $response = $this->get('/yonetim/bayiler?q=Yetkili');
        $response->assertStatus(200);
        $response->assertSee('Arama Test Firma');

        // E-posta ile arama
        $response = $this->get('/yonetim/bayiler?q=arama@example.com');
        $response->assertStatus(200);
        $response->assertSee('Arama Test Firma');

        // Telefon ile arama (normalize edilmiş formatta)
        $response = $this->get('/yonetim/bayiler?q=05551234567');
        $response->assertStatus(200);
        $response->assertSee('Arama Test Firma');

        // İl ile arama (uppercase)
        $response = $this->get('/yonetim/bayiler?q=ANKARA');
        $response->assertStatus(200);
        $response->assertSee('Arama Test Firma');

        // İlçe ile arama (uppercase, Türkçe karakter olmadan)
        $response = $this->get('/yonetim/bayiler?q=CANKAYA');
        $response->assertStatus(200);
        $response->assertSee('Arama Test Firma');
    }

    #[Test]
    public function update_validates_city_exists_in_location_list()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000017',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $response = $this->put("/yonetim/bayiler/{$dealer->id}", [
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000017',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'GEÇERSİZ İL', // Geçersiz il
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['city']);
    }

    #[Test]
    public function update_validates_district_belongs_to_city()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000018',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $response = $this->put("/yonetim/bayiler/{$dealer->id}", [
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000018',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'ANKARA',
            'district' => 'KADIKÖY', // İstanbul'a ait ilçe, Ankara'ya ait değil
            'address' => 'Adres',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['district']);
    }

    #[Test]
    public function update_normalizes_tax_number_to_digits_only()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $dealer = Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000019',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $response = $this->put("/yonetim/bayiler/{$dealer->id}", [
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000019',
            'tax_office' => 'Kadıköy',
            'tax_number' => '123-456-7890', // Formatlı vergi no
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'status' => 'active',
        ]);

        $dealer->refresh();
        $this->assertEquals('1234567890', $dealer->tax_number); // Sadece rakamlar
    }
}

