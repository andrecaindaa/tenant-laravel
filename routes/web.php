<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\BillingLog;

/*
|--------------------------------------------------------------------------
| Home & Redirects
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    if (!session('tenant_id')) {
        return redirect()->route('select-tenant');
    }

    // Se onboarding não está completo, redireciona
    $tenant = app('currentTenant');
    if ($tenant && method_exists($tenant, 'onboardingCompleted') && !$tenant->onboardingCompleted()) {
        return redirect()->route('onboarding');
    }

    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('/register', function (Request $request) {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        // Criar utilizador
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Criar tenant automaticamente
        $tenant = Tenant::create([
            'name' => $user->name . "'s Workspace",
            'slug' => Str::slug($user->name) . '-' . Str::random(4),
            'owner_id' => $user->id,
        ]);

        // Associar user ao tenant como owner
        $user->tenants()->attach($tenant->id, [
            'role' => 'owner',
        ]);

        // Criar subscrição com trial (14 dias por padrão)
        $freePlan = Plan::where('slug', 'free')->first();
        if ($freePlan) {
            $tenant->subscriptions()->create([
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'trial_ends_at' => now()->addDays(14), // 14 dias de trial padrão
            ]);
        }

        // Login automático
        Auth::login($user);

        // Guardar tenant ativo
        session(['tenant_id' => $tenant->id]);

        return redirect()->route('dashboard');
    });

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
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Tenant Selection & Management
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/select-tenant', function () {
        $user = Auth::user();
        $tenants = $user->tenants()->with('subscription.plan')->get();

        return view('tenant.select', compact('tenants'));
    })->name('select-tenant');

    Route::post('/select-tenant/{tenant}', function (Tenant $tenant) {
        // Verificar se o usuário tem acesso
        if (!Auth::user()->tenants()->where('tenants.id', $tenant->id)->exists()) {
            abort(403, 'Access denied');
        }

        session(['tenant_id' => $tenant->id]);
        return redirect()->route('dashboard');
    })->name('tenant.select');

   Route::get('/tenants/create', function () {
    // Forçar seleção de tenant primeiro se não houver
    if (!session('tenant_id')) {
        $user = Auth::user();
        $tenants = $user->tenants;

        if ($tenants->count() === 1) {
            session(['tenant_id' => $tenants->first()->id]);
        } else {
            return redirect()->route('select-tenant');
        }
    }

    $freePlan = Plan::where('slug', 'free')->first();

    // Se houver tenant_id na sessão, buscar o tenant para o layout
    $tenant = null;
    if (session('tenant_id')) {
        $tenant = Tenant::find(session('tenant_id'));
    }

    return view('tenant.create', [
        'freePlan' => $freePlan,
        'tenant' => $tenant, // Passar tenant para o layout
    ]);
})->middleware('auth')->name('tenants.create');

    Route::post('/tenants', function (Request $request) {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $tenant = Tenant::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(4),
            'owner_id' => auth()->id(),
        ]);

        Auth::user()->tenants()->attach($tenant->id, [
            'role' => 'owner',
        ]);

        // Criar subscrição free
        $freePlan = Plan::where('slug', 'free')->first();
        if ($freePlan) {
            $tenant->subscriptions()->create([
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'trial_ends_at' => now()->addDays(14),
            ]);
        }

        session(['tenant_id' => $tenant->id]);

        return redirect()->route('dashboard');
    })->name('tenants.store');
});

/*
|--------------------------------------------------------------------------
| Onboarding Wizard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/onboarding', function () {
        $tenant = app('currentTenant');

        // Verificar se o método existe
        if (method_exists($tenant, 'onboardingCompleted') && $tenant->onboardingCompleted()) {
            return redirect()->route('dashboard');
        }

        // Se não tem método, redirecionar direto
        if (!method_exists($tenant, 'onboardingCompleted')) {
            return redirect()->route('dashboard');
        }

        $steps = $tenant->onboarding_steps ?? [];
        $progress = method_exists($tenant, 'currentOnboardingProgress') ? $tenant->currentOnboardingProgress() : 100;
        $currentStep = null;

        foreach ($steps as $key => $step) {
            if (!$step['completed']) {
                $currentStep = $key;
                break;
            }
        }

        return view('onboarding.wizard', compact('tenant', 'steps', 'progress', 'currentStep'));
    })->name('onboarding');

    Route::post('/onboarding/{step}', function (Request $request, $step) {
        $tenant = app('currentTenant');
        $steps = $tenant->onboarding_steps ?? [];

        if (isset($steps[$step])) {
            $steps[$step]['completed'] = true;
            $tenant->update(['onboarding_steps' => $steps]);

            // Se todos obrigatórios estão completos
            $allRequired = true;
            foreach ($steps as $s) {
                if ($s['required'] && !$s['completed']) {
                    $allRequired = false;
                    break;
                }
            }

            if ($allRequired && method_exists($tenant, 'completeOnboarding')) {
                $tenant->completeOnboarding();
                return redirect()->route('dashboard')->with('success', 'Onboarding completado!');
            }

            return back()->with('success', 'Passo ' . $steps[$step]['title'] . ' completado');
        }

        return back()->with('error', 'Passo inválido');
    });

    Route::post('/onboarding/skip', function () {
        $tenant = app('currentTenant');
        if (method_exists($tenant, 'completeOnboarding')) {
            $tenant->completeOnboarding();
        }
        return redirect()->route('dashboard')->with('info', 'Onboarding saltado');
    })->name('onboarding.skip');
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $tenant = app('currentTenant');

    // Usar métodos seguros
    $subscription = $tenant->subscription ?? $tenant->subscriptions()->active()->first();
    $plan = $subscription?->plan ?? Plan::where('slug', 'free')->first();

    // Verificar métodos antes de chamar
    $onTrial = method_exists($tenant, 'onTrial') ? $tenant->onTrial() : false;
    $trialDaysLeft = method_exists($tenant, 'trialDaysLeft') ? $tenant->trialDaysLeft() : 0;
    $usage = method_exists($tenant, 'usage') ? $tenant->usage() : ['users' => 0, 'projects' => 0];
    $hasReachedLimitUsers = method_exists($tenant, 'hasReachedLimit') ? $tenant->hasReachedLimit('users') : false;
    $hasReachedLimitProjects = method_exists($tenant, 'hasReachedLimit') ? $tenant->hasReachedLimit('projects') : false;

    return view('dashboard', [
        'tenant'       => $tenant,
        'subscription' => $subscription,
        'plan'         => $plan,
        'limits'       => $plan->limits ?? [],
        'usage'        => $usage,
        'onTrial'      => $onTrial,
        'trialDaysLeft'=> $trialDaysLeft,
        'trialEndsAt'  => $subscription?->trial_ends_at,
        'recentLogs'   => BillingLog::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(),
        'reachedLimits' => [
            'users' => $hasReachedLimitUsers,
            'projects' => $hasReachedLimitProjects,
        ],
    ]);
})->middleware(['auth', 'tenant'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Plans & Billing - CORREÇÕES IMPORTANTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tenant'])->prefix('billing')->group(function () {
    Route::get('/plans', function () {
        $tenant = app('currentTenant');

        // filtrar por 'active' = true
        $plans = Plan::where('active', true)
            ->orderBy('price', 'asc')
            ->get();

        // Usar currentPlan() em vez de plan
        $currentPlan = method_exists($tenant, 'currentPlan') ? $tenant->currentPlan() : null;
        $subscription = $tenant->subscription ?? $tenant->subscriptions()->active()->first();

        return view('billing.plans', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
            'tenant' => $tenant,
            'trialEndsAt' => $subscription?->trial_ends_at,
        ]);
    })->name('billing.plans');

    // CORREÇÃO: Rota de upgrade correta
    Route::post('/plans/{plan}/upgrade', function (Request $request, Plan $plan) {
        $tenant = app('currentTenant');

        if (!$tenant) {
            return redirect()->back()->with('error', 'Tenant não encontrado.');
        }

        // Verificar se já está no mesmo plano
        $currentPlan = method_exists($tenant, 'currentPlan') ? $tenant->currentPlan() : null;
        if ($currentPlan && $currentPlan->id === $plan->id) {
            return redirect()->back()->with('error', 'Já está neste plano.');
        }

        // Verificar se é um downgrade
        $isDowngrade = false;
        if ($currentPlan && $plan->price < $currentPlan->price) {
            $isDowngrade = true;
        }

        try {
            // Buscar ou criar subscription
            $subscription = $tenant->subscription ?? $tenant->subscriptions()->active()->first();

            if (!$subscription) {
                // Criar nova subscription
                $subscription = $tenant->subscriptions()->create([
                    'plan_id' => $plan->id,
                    'status' => 'active',
                ]);
            } else {
                // Atualizar subscription existente
                $subscription->update([
                    'plan_id' => $plan->id,
                ]);
            }

            // Registrar no log de billing
            BillingLog::create([
                'tenant_id' => $tenant->id,
                'from_plan' => $currentPlan?->id,
                'to_plan' => $plan->id,
                'type' => $isDowngrade ? 'downgrade' : 'upgrade',
                'meta' => [
                    'user_id' => auth()->id(),
                    'timestamp' => now(),
                ],
            ]);

            return redirect()->route('billing.plans')
                ->with('success', $isDowngrade ? 'Plano alterado com sucesso!' : 'Upgrade realizado com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao alterar plano: ' . $e->getMessage());
        }
    })->name('billing.upgrade');

    // CORREÇÃO: Rota de downgrade
    Route::post('/plans/{plan}/downgrade', function (Request $request, Plan $plan) {
        $tenant = app('currentTenant');
        $currentPlan = method_exists($tenant, 'currentPlan') ? $tenant->currentPlan() : null;

        if (!$currentPlan) {
            return back()->with('error', 'Plano atual não encontrado.');
        }

        // CORREÇÃO: Usar price em vez de price_monthly
        if ($plan->price >= $currentPlan->price) {
            return back()->with('error', 'Selecione um plano inferior para downgrade');
        }

        // Agenda downgrade para o próximo mês
        $nextMonth = now()->addMonth();

        // Buscar subscription ativa
        $subscription = $tenant->subscription ?? $tenant->subscriptions()->active()->first();

        if ($subscription) {
            $subscription->update([
                'next_plan_id' => $plan->id,
                'next_plan_starts_at' => $nextMonth,
            ]);
        }

        // Log
        BillingLog::create([
            'tenant_id' => $tenant->id,
            'from_plan' => $currentPlan->id,
            'to_plan' => $plan->id,
            'type' => 'downgrade_scheduled',
            'meta' => [
                'user_id' => auth()->id(),
                'scheduled_for' => $nextMonth->toISOString(),
            ],
        ]);

        return back()->with('success', 'Downgrade agendado para ' . $nextMonth->format('d/m/Y'));
    })->name('billing.downgrade');

    Route::post('/cancel-downgrade', function () {
        $tenant = app('currentTenant');

        // Buscar subscription ativa
        $subscription = $tenant->subscription ?? $tenant->subscriptions()->active()->first();

        if ($subscription) {
            $subscription->update([
                'next_plan_id' => null,
                'next_plan_starts_at' => null,
            ]);
        }

        BillingLog::create([
            'tenant_id' => $tenant->id,
            'type' => 'downgrade_cancelled',
            'meta' => [
                'user_id' => auth()->id(),
            ],
        ]);

        return back()->with('success', 'Downgrade cancelado');
    })->name('billing.cancel-downgrade');

    Route::get('/history', function () {
        $tenant = app('currentTenant');
        $logs = BillingLog::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('billing.history', compact('logs', 'tenant'));
    })->name('billing.history');
});


/*
|--------------------------------------------------------------------------
| Admin (Plan Management)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/plans', function () {
        $plans = Plan::all();
        return view('admin.plans.index', compact('plans'));
    })->name('admin.plans');

    Route::get('/plans/create', function () {
        return view('admin.plans.create');
    })->name('admin.plans.create');

    Route::post('/plans', function (Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:plans,slug',
            'price' => 'required|numeric|min:0',
            'interval' => 'nullable|in:monthly,yearly',
            'limits' => 'nullable|array',
            'active' => 'boolean',
        ]);

        // Processar limites
        if (isset($data['limits'])) {
            foreach ($data['limits'] as $key => $value) {
                if ($value === '' || $value === null) {
                    $data['limits'][$key] = null;
                } elseif (is_numeric($value)) {
                    $data['limits'][$key] = (int)$value;
                }
            }
        }

        Plan::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'price' => $data['price'],
            'interval' => $data['interval'] ?? null,
            'limits' => $data['limits'] ?? [],
            'active' => $data['active'] ?? true,
        ]);

        return redirect()->route('admin.plans')->with('success', 'Plano criado com sucesso');
    })->name('admin.plans.store');

    // Adicionar rota para editar plano
    Route::get('/plans/{plan}/edit', function (Plan $plan) {
        return view('admin.plans.edit', compact('plan'));
    })->name('admin.plans.edit');

    // Adicionar rota para atualizar plano
    Route::put('/plans/{plan}', function (Request $request, Plan $plan) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,' . $plan->id,
            'price' => 'required|numeric|min:0',
            'interval' => 'nullable|in:monthly,yearly',
            'limits' => 'nullable|array',
            'active' => 'boolean',
        ]);

        // Processar limites
        if (isset($data['limits'])) {
            foreach ($data['limits'] as $key => $value) {
                if ($value === '' || $value === null) {
                    $data['limits'][$key] = null;
                } elseif (is_numeric($value)) {
                    $data['limits'][$key] = (int)$value;
                }
            }
        }

        $plan->update($data);

        return redirect()->route('admin.plans')->with('success', 'Plano atualizado com sucesso');
    })->name('admin.plans.update');

    // Adicionar rota para excluir plano
    Route::delete('/plans/{plan}', function (Plan $plan) {
        // Verificar se há subscriptions usando este plano
        if ($plan->subscriptions()->exists()) {
            return redirect()->route('admin.plans')
                ->with('error', 'Não é possível excluir este plano porque existem tenants usando-o.');
        }

        $plan->delete();

        return redirect()->route('admin.plans')->with('success', 'Plano excluído com sucesso');
    })->name('admin.plans.destroy');
});
