<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function create()
    {
        return view('session/login');
    }

    public function store()
    {
        $attributes = request()->validate([
            'email'=>'required|email',
            'password' => 'required',
            'remember' => 'string',
        ]);

        $remember = isset($attributes['remember']) && $attributes['remember'] == 'on';
        unset($attributes['remember']);
        if (Auth::attempt($attributes, $remember))
        {
            session()->regenerate();
            return redirect('/');
        }

        return back()->withErrors(['msgError' => 'These credentials do not match our records.']);
    }

    public function destroy()
    {

        Auth::logout();

        return redirect('/login')->with(['success'=>'You\'ve been logged out.']);
    }
}
