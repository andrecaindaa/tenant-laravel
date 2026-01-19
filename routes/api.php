<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\Plan;
use App\Models\BillingLog;

/*
|--------------------------------------------------------------------------
| Billing — Upgrade de plano
|--------------------------------------------------------------------------
*/

Route::post('/billing/upgrade/{plan}', function (Plan $plan) {

    $tenant = app('currentTenant');
    $subscription = $tenant->subscription;

    $fromPlan = $subscription->plan_id;

    $subscription->update([
        'plan_id' => $plan->id,
        'status'  => 'active',
    ]);

    BillingLog::create([
        'tenant_id' => $tenant->id,
        'from_plan' => $fromPlan,
        'to_plan'   => $plan->id,
        'type'      => 'upgrade',
    ]);

    return response()->json(['plan' => $plan]);

})->middleware(['auth', 'tenant']);




Route::post('/billing/downgrade/{plan}', function (Plan $plan) {
    $tenant = app('currentTenant');
    $subscription = $tenant->subscription;

    $subscription->update([
        'next_plan_id' => $plan->id,
    ]);

    BillingLog::create([
        'tenant_id' => $tenant->id,
        'from_plan' => $subscription->plan_id,
        'to_plan'   => $plan->id,
        'type'      => 'downgrade_scheduled',
    ]);

    return response()->json([
        'message' => 'Downgrade scheduled for next billing cycle',
        'next_plan' => $plan,
    ]);
})->middleware(['auth', 'tenant']);



Route::get('/billing', function () {
    $tenant = app('currentTenant');
    $subscription = $tenant->subscription;

    return response()->json([
        'plan' => $subscription?->plan,
        'status' => $subscription?->status,
        'on_trial' => $subscription?->onTrial() ?? false,
        'trial_ends_at' => $subscription?->trial_ends_at,
        'usage' => $tenant->usage(),
    ]);
})->middleware(['auth', 'tenant']);


/*
|--------------------------------------------------------------------------
| Auth / Session
|--------------------------------------------------------------------------
*/
Route::get('/me', function (Request $request) {
    return response()->json([
        'user'      => $request->user(),
        'tenant_id' => session('tenant_id'),
    ]);
})->middleware('auth');

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
})->middleware('auth');
