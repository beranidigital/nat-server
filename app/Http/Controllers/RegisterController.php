<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function create()
    {
        return view('session/register');
    }

    public function store()
    {

        $attributes = request()->validate([
            'role_id' => 'required',
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users', 'email')],
            'password' => ['required', 'min:5', 'max:20'],
            'agreement' => ['accepted']
        ]);
        $insertUser = User::create(
            [
                'role_id' => $attributes['role_id'],
                'first_name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => Hash::make($attributes['password']),
            ]
        );

        session()->flash('success', 'Your account has been created.');
        Auth::login($insertUser);
        return redirect('/');
    }
}
