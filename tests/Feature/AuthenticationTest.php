<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_logout_with_database_authentication(): void
    {
        $this->createUser('staff', 'staff@example.com', 'password123');

        $this->get(route('login'))->assertOk();

        $this->post(route('login.store'), [
            'email' => 'staff@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))->assertOk();

        $this->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_admin_only_routes(): void
    {
        $this->createUser('staff', 'staff@example.com', 'password123');

        $this->post(route('login.store'), [
            'email' => 'staff@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('products.create'))->assertForbidden();
        $this->get(route('categories.index'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_login_rejects_an_incorrect_password(): void
    {
        $this->createUser('admin', 'admin@example.com', 'password123');

        $this->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => 'incorrect',
        ])->assertSessionHasErrors('email');
    }

    private function createUser(string $role, string $email, string $password): void
    {
        DB::table('users')->insert([
            'name' => ucfirst($role).' User',
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
