<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'users:create-admin';

    protected $description = 'Tạo tài khoản admin đầu tiên một cách an toàn';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Họ tên admin'));
        $email = trim((string) $this->ask('Email admin'));

        if ($name === '' || strlen($name) > 255) {
            $this->error('Họ tên không được để trống và tối đa 255 ký tự.');

            return self::FAILURE;
        }

        if (strlen($email) > 255 || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Email không hợp lệ.');

            return self::FAILURE;
        }

        if (DB::table('users')->where('email', $email)->exists()) {
            $this->error('Email đã tồn tại. Hãy dùng chức năng quản lý tài khoản để thay đổi quyền.');

            return self::FAILURE;
        }

        $password = $this->secret('Mật khẩu (ít nhất 8 ký tự)');

        if (! is_string($password) || strlen($password) < 8) {
            $this->error('Mật khẩu phải có ít nhất 8 ký tự.');

            return self::FAILURE;
        }

        DB::table('users')->insert([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info('Đã tạo tài khoản admin.');

        return self::SUCCESS;
    }
}
