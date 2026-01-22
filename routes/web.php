<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Models\Tenant;
use App\Models\Plan;

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
    $tenants = $user->tenants;

    return view('select-tenant', compact('tenants'));
})->middleware('auth')->name('select-tenant');

Route::post('/select-tenant/{tenant}', function (Tenant $tenant) {
    session(['tenant_id' => $tenant->id]);

    return redirect()->route('dashboard');
})->middleware('auth');

/*
|--------------------------------------------------------------------------
| Create Tenant
|--------------------------------------------------------------------------
*/
Route::post('/tenants', function (Request $request) {
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
    ]);

    $tenant = Tenant::create([
        'name' => $request->name,
        'slug' => Str::slug($request->name),
    ]);

    Auth::user()->tenants()->attach($tenant->id, [
        'role' => 'owner',
    ]);

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

/*
|--------------------------------------------------------------------------
| Subscription
|--------------------------------------------------------------------------
*/
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


Route::get('/plans', function () {
    return view('plans.index', [
        'plans' => Plan::all(),
    ]);
})->middleware('auth');

Route::post('/plans', function (Request $request) {
    $data = $request->validate([
        'name'   => 'required',
        'price'  => 'required|numeric',
        'limits' => 'required|array',
    ]);

    Plan::create([
        'name'   => $data['name'],
        'price'  => $data['price'],
        'limits' => $data['limits'],
        'active' => true,
    ]);

    return back();
})->middleware('auth');
