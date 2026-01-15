<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/login', function (Request $request) {

    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    $request->session()->regenerate();

    $user = $request->user();

    return response()->json([
        'user' => $user,
        'tenants' => $user->tenants()->get([
            'tenants.id',
            'tenants.name',
            'tenants.slug',
            'tenant_user.role',
        ]),
    ]);
});

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json([
        'message' => 'Logged out'
    ]);
})->middleware('auth:sanctum');
