<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_staff_with_a_hashed_password(): void
    {
        $this->authenticateAdmin();

        $this->post(route('users.store'), [
            'name' => 'Nhân viên mới',
            'email' => 'staff@example.com',
            'role' => 'staff',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ])->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'Tạo tài khoản thành công!');

        $account = DB::table('users')->where('email', 'staff@example.com')->first();
        $this->assertNotNull($account);
        $this->assertSame('staff', $account->role);
        $this->assertTrue(Hash::check('secure-pass-123', $account->password));
        $this->assertNotSame('secure-pass-123', $account->password);
    }

    public function test_admin_cannot_delete_the_last_admin_or_their_own_account(): void
    {
        $adminId = $this->authenticateAdmin();

        $this->delete(route('users.destroy', $adminId))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'Bạn không thể xóa tài khoản đang đăng nhập.');

        DB::table('users')->insert([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->put(route('users.update', $adminId), [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'staff',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $adminId, 'role' => 'admin']);
    }

    private function authenticateAdmin(): int
    {
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        return $adminId;
    }
}
