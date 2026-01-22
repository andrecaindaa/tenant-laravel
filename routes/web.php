<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\Tenant;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    if (!session('tenant_id')) {
        return redirect()->route('select-tenant');
    }

    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (!Auth::attempt($credentials)) {
        return back()->withErrors([
            'email' => 'Credenciais inválidas',
        ]);
    }

    $request->session()->regenerate();

    return redirect('/');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Tenant selection
|--------------------------------------------------------------------------
*/
Route::get('/select-tenant', function () {
    $user = Auth::user();

    // Ajusta conforme a tua relação (ex: $user->tenants)
    $tenants = $user->tenants;

    return view('select-tenant', compact('tenants'));
})->middleware('auth')->name('select-tenant');

Route::post('/select-tenant/{tenant}', function (Tenant $tenant) {
    session(['tenant_id' => $tenant->id]);

    return redirect()->route('dashboard');
})->middleware('auth');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $tenant = app('currentTenant');
    $subscription = $tenant->subscription;

    return view('dashboard', [
        'tenant'       => $tenant,
        'subscription' => $subscription,
        'limits'       => $subscription?->plan->limits ?? [],
        'usage'        => $tenant->usage(),
        'onTrial'      => $subscription?->onTrial(),
        'trialEndsAt'  => $subscription?->trial_ends_at,
    ]);
})->middleware(['auth', 'tenant'])->name('dashboard');


Route::post('/subscription/change', function (Request $request) {
    $request->validate([
        'plan_id' => ['required', 'exists:plans,id'],
    ]);

    $tenant = app('currentTenant');

    $tenant->subscription()->update([
        'plan_id' => $request->plan_id,
    ]);

    return back()->with('success', 'Plano atualizado com sucesso.');
})->middleware(['auth', 'tenant']);
