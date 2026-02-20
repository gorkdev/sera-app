<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Dealer;
use App\Models\DealerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminGroupManagementTest extends TestCase
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
    public function admin_can_view_groups_list()
    {
        $this->actingAsAdmin();

        DealerGroup::factory()->create(['name' => 'VIP Bayiler', 'code' => 'VIP']);
        DealerGroup::factory()->create(['name' => 'Standart Bayiler', 'code' => 'STANDART']);

        $response = $this->get('/yonetim/gruplar');

        $response->assertStatus(200);
        $response->assertSee('VIP Bayiler');
        $response->assertSee('Standart Bayiler');
    }

    #[Test]
    public function admin_can_search_groups()
    {
        $this->actingAsAdmin();

        DealerGroup::factory()->create(['name' => 'VIP Bayiler', 'code' => 'VIP']);
        DealerGroup::factory()->create(['name' => 'Standart Bayiler', 'code' => 'STANDART']);

        $response = $this->get('/yonetim/gruplar?q=VIP');
        $response->assertStatus(200);
        $response->assertSee('VIP Bayiler');
        $response->assertDontSee('Standart Bayiler');

        $response = $this->get('/yonetim/gruplar?q=STANDART');
        $response->assertStatus(200);
        $response->assertSee('Standart Bayiler');
        $response->assertDontSee('VIP Bayiler');
    }

    #[Test]
    public function admin_can_view_create_group_page()
    {
        $this->actingAsAdmin();

        $response = $this->get('/yonetim/gruplar/olustur');

        $response->assertStatus(200);
        $response->assertSee('Yeni Bayi Grubu');
    }

    #[Test]
    public function admin_can_create_group()
    {
        $this->actingAsAdmin();

        $response = $this->post('/yonetim/gruplar', [
            'name' => 'VIP Bayiler',
            'code' => 'VIP',
            'delay_minutes' => 0,
            'is_default' => false,
            'sort_order' => 0,
        ]);

        $response->assertRedirect('/yonetim/gruplar');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('dealer_groups', [
            'name' => 'VIP Bayiler',
            'code' => 'VIP',
            'delay_minutes' => 0,
            'is_default' => false,
        ]);
    }

    #[Test]
    public function admin_can_create_default_group()
    {
        $this->actingAsAdmin();

        // Önce bir varsayılan grup oluştur
        $existingDefault = DealerGroup::factory()->create(['is_default' => true]);

        $response = $this->post('/yonetim/gruplar', [
            'name' => 'Yeni Varsayılan',
            'code' => 'NEW_DEFAULT',
            'delay_minutes' => 30,
            'is_default' => true,
            'sort_order' => 0,
        ]);

        $response->assertRedirect('/yonetim/gruplar');
        $response->assertSessionHas('success');

        // Yeni grup varsayılan olmalı
        $this->assertDatabaseHas('dealer_groups', [
            'code' => 'NEW_DEFAULT',
            'is_default' => true,
        ]);

        // Eski grup varsayılan olmamalı
        $this->assertDatabaseHas('dealer_groups', [
            'id' => $existingDefault->id,
            'is_default' => false,
        ]);
    }

    #[Test]
    public function create_validates_required_fields()
    {
        $this->actingAsAdmin();

        $response = $this->post('/yonetim/gruplar', []);

        $response->assertSessionHasErrors(['name', 'code', 'delay_minutes']);
    }

    #[Test]
    public function create_validates_unique_code()
    {
        $this->actingAsAdmin();

        DealerGroup::factory()->create(['code' => 'VIP']);

        $response = $this->post('/yonetim/gruplar', [
            'name' => 'Test Grup',
            'code' => 'VIP',
            'delay_minutes' => 0,
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    #[Test]
    public function create_validates_delay_minutes_minimum()
    {
        $this->actingAsAdmin();

        $response = $this->post('/yonetim/gruplar', [
            'name' => 'Test Grup',
            'code' => 'TEST',
            'delay_minutes' => -1,
        ]);

        $response->assertSessionHasErrors(['delay_minutes']);
    }

    #[Test]
    public function admin_can_view_edit_group_page()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['name' => 'Test Grup']);

        $response = $this->get("/yonetim/gruplar/{$group->id}");

        $response->assertStatus(200);
        $response->assertSee('Bayi Grubu Düzenle');
        $response->assertSee('Test Grup');
    }

    #[Test]
    public function admin_can_update_group()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create([
            'name' => 'Eski Ad',
            'code' => 'OLD',
            'delay_minutes' => 15,
        ]);

        $response = $this->put("/yonetim/gruplar/{$group->id}", [
            'name' => 'Yeni Ad',
            'code' => 'NEW',
            'delay_minutes' => 30,
            'is_default' => false,
            'sort_order' => 5,
        ]);

        $response->assertRedirect('/yonetim/gruplar');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('dealer_groups', [
            'id' => $group->id,
            'name' => 'Yeni Ad',
            'code' => 'NEW',
            'delay_minutes' => 30,
            'sort_order' => 5,
        ]);
    }

    #[Test]
    public function update_validates_unique_code_excluding_current()
    {
        $this->actingAsAdmin();

        $group1 = DealerGroup::factory()->create(['code' => 'VIP']);
        $group2 = DealerGroup::factory()->create(['code' => 'STANDART']);

        // group2'yi group1'in koduna güncellemeye çalış
        $response = $this->put("/yonetim/gruplar/{$group2->id}", [
            'name' => 'Standart Bayiler',
            'code' => 'VIP',
            'delay_minutes' => 15,
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    #[Test]
    public function update_allows_same_code_for_current_group()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['code' => 'VIP']);

        $response = $this->put("/yonetim/gruplar/{$group->id}", [
            'name' => 'VIP Bayiler Güncellendi',
            'code' => 'VIP', // Aynı kod
            'delay_minutes' => 0,
        ]);

        $response->assertRedirect('/yonetim/gruplar');
        $response->assertSessionHas('success');
    }

    #[Test]
    public function admin_can_delete_group_without_dealers()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['name' => 'Silinecek Grup']);

        $response = $this->delete("/yonetim/gruplar/{$group->id}");

        $response->assertRedirect('/yonetim/gruplar');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('dealer_groups', ['id' => $group->id]);
    }

    #[Test]
    public function admin_cannot_delete_group_with_dealers()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create();
        Dealer::create([
            'dealer_group_id' => $group->id,
            'company_name' => 'Test Firma',
            'contact_name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05550000001',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'city' => 'İSTANBUL',
            'district' => 'KADIKÖY',
            'address' => 'Adres',
            'kvkk_consent' => true,
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $response = $this->delete("/yonetim/gruplar/{$group->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('dealer_groups', ['id' => $group->id]);
    }

    #[Test]
    public function admin_cannot_delete_default_group()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['is_default' => true]);

        $response = $this->delete("/yonetim/gruplar/{$group->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('dealer_groups', ['id' => $group->id]);
    }

    #[Test]
    public function groups_list_shows_dealer_count()
    {
        $this->actingAsAdmin();

        $group = DealerGroup::factory()->create(['name' => 'Test Grup']);
        for ($i = 1; $i <= 3; $i++) {
            Dealer::create([
                'dealer_group_id' => $group->id,
                'company_name' => "Test Firma {$i}",
                'contact_name' => "Test Kullanıcı {$i}",
                'email' => "test{$i}@example.com",
                'phone' => '0555000000' . $i,
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

        $response = $this->get('/yonetim/gruplar');

        $response->assertStatus(200);
        $response->assertSee('Test Grup');
        $response->assertSee('3'); // Bayi sayısı
    }

    #[Test]
    public function groups_list_shows_delay_minutes()
    {
        $this->actingAsAdmin();

        DealerGroup::factory()->create(['name' => 'VIP', 'delay_minutes' => 0]);
        DealerGroup::factory()->create(['name' => 'Standart', 'delay_minutes' => 15]);

        $response = $this->get('/yonetim/gruplar');

        $response->assertStatus(200);
        $response->assertSee('Anında'); // 0 dakika için
        $response->assertSee('15 dakika');
    }
}
