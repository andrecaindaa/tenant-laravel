<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Tenant;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'tenant'])
    ->get('/billing', function (Request $request) {

        $tenant = app('currentTenant');
        $plan   = $tenant->plan();

        return response()->json([
            'tenant' => [
                'id'   => $tenant->id,
                'name' => $tenant->name,
            ],

            'plan' => $plan ? [
                'name'   => $plan->name,
                'slug'   => $plan->slug,
                'price'  => $plan->price,
                'limits' => $plan->limits,
            ] : null,

            'usage' => $tenant->usage(),
        ]);
    });




Route::post('/projects')
    ->middleware(['auth', 'tenant', 'limit:projects']);


Route::get('/me', function (Request $request) {
    $user = $request->user();

    return response()->json([
        'user' => $user,
        'tenant_id' => session('tenant_id'),
        'permissions' => $user
            ?->permissionsInTenant(),
    ]);
})->middleware('auth');



Route::middleware(['auth', 'tenant', 'can:users.manage'])
    ->post('/users', function () {
        //  criar user no tenant
    });


Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Tenant Selection
|--------------------------------------------------------------------------
| O utilizador escolhe qual tenant fica ativo na sessão
| Requer autenticação, mas NÃO requer tenant ativo
*/
Route::post('/select-tenant/{tenant}', function (Request $request, Tenant $tenant) {
    $user = $request->user();

    if (
        !$user ||
        !$user->tenants()
            ->where('tenants.id', $tenant->id)
            ->exists()
    ) {
        abort(403, 'Unauthorized tenant access');
    }

    session(['tenant_id' => $tenant->id]);

    return response()->json([
        'message' => 'Tenant selected successfully',
        'tenant'  => [
            'id'   => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
        ],
    ]);
})->middleware('auth');

/*
|--------------------------------------------------------------------------
| Tenant Protected Routes
|--------------------------------------------------------------------------
| Todas as rotas aqui:
| - requerem utilizador autenticado
| - requerem tenant ativo válido
*/
Route::middleware(['auth', 'tenant'])->group(function () {

    Route::get('/dashboard', function () {
        $tenant = app('currentTenant');

        return response()->json([
            'tenant_id'   => $tenant->id,
            'tenant_name' => $tenant->name,
        ]);
    });

});
