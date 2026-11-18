<?php

namespace App\Http\Controllers;

use App\Services\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = DB::table('users')
            ->select('id', 'name', 'email', 'role', 'is_active', 'created_at')
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', ['account' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|in:admin,staff',
            'is_active' => 'sometimes|boolean',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        DB::table('users')->insert([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? 1,
            'password' => Hash::make($validated['password']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Tạo tài khoản thành công!');
    }

    public function edit(int $id)
    {
        $account = DB::table('users')
            ->select('id', 'name', 'email', 'role', 'is_active')
            ->find($id);

        if (! $account) {
            abort(404);
        }

        return view('users.form', compact('account'));
    }

    public function update(Request $request, int $id)
    {
        $account = DB::table('users')->where('id', $id)->first();

        if (! $account) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$id,
            'role' => 'required|in:admin,staff',
            'is_active' => 'sometimes|boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        $adminCount = DB::table('users')->where('role', 'admin')->where('is_active', 1)->count();
        $demotingLastAdmin = $account->role === 'admin'
            && ($validated['role'] !== 'admin' || ! ($validated['is_active'] ?? 1))
            && $adminCount <= 1;

        if ($demotingLastAdmin || ((int) $request->user()->id === $id && ! ($validated['is_active'] ?? 1))) {
            return back()->withInput()
                ->withErrors(['role' => 'Không thể hạ quyền tài khoản admin cuối cùng.']);
        }

        $updates = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? 1,
            'updated_at' => now(),
        ];

        if (! empty($validated['password'])) {
            $updates['password'] = Hash::make($validated['password']);
        }

        DB::transaction(function () use ($id, $updates, $account) {
            DB::table('users')->where('id', $id)->update($updates);
            AuditLog::record('UPDATE_USER', 'users', $id, $account, $updates);
        });

        return redirect()->route('users.index')
            ->with('success', 'Cập nhật tài khoản thành công!');
    }

    public function destroy(Request $request, int $id)
    {
        $account = DB::table('users')->where('id', $id)->first();

        if (! $account) {
            abort(404);
        }

        if ((int) $request->user()->id === $id) {
            return redirect()->route('users.index')
                ->with('error', 'Bạn không thể xóa tài khoản đang đăng nhập.');
        }

        if ($account->role === 'admin' && DB::table('users')->where('role', 'admin')->where('is_active', 1)->count() <= 1) {
            return redirect()->route('users.index')
                ->with('error', 'Không thể xóa tài khoản admin cuối cùng.');
        }

        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('users.index')
            ->with('success', 'Xóa tài khoản thành công!');
    }
}
