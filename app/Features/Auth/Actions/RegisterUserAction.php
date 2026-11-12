<?php

namespace App\Features\Auth\Actions;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    /**
     * Daftarkan pengguna baru dengan peran anggota (member) dan langsung login.
     *
     * @param  array{name: string, username: string, email: string, password: string}  $data
     */
    public function execute(Request $request, array $data): User
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return $user;
    }
}
