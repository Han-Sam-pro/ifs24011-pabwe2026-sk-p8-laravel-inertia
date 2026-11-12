<?php

namespace App\Features\Auth\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginUserAction
{
    /**
     * @param  array{username: string, password: string}  $credentials
     */
    public function execute(Request $request, array $credentials, bool $remember = false): bool
    {
        if (! Auth::attempt($credentials, $remember)) {
            return false;
        }

        $request->session()->regenerate();

        return true;
    }
}
