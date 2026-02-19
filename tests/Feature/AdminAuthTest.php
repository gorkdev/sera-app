<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_login_page_loads()
    {
        $response = $this->get('/yonetim/giris');

        $response->assertStatus(200);
        $response->assertSee('Yönetim Paneli');
    }

    #[Test]
    public function admin_can_login_with_valid_credentials()
    {
        $admin = AdminUser::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    #[Test]
    public function inactive_admin_cannot_login()
    {
        $admin = AdminUser::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('secret123'),
            'is_active' => false,
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('credentials');
        $this->assertGuest('admin');
    }
}

