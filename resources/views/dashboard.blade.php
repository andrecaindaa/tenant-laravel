@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="card">
    <h2>Plano atual</h2>

    @if($subscription)
        <p><strong>{{ $subscription->plan->name }}</strong></p>

        @if($onTrial)
            <p>Trial termina em {{ $trialEndsAt->format('d/m/Y') }}</p>
        @endif
    @else
        <p>Sem subscrição ativa</p>
    @endif
</div>

<div class="card">
    <h2>Limites</h2>

    <div class="grid">
        @foreach($limits as $key => $limit)
            <div class="card">
                <strong>{{ ucfirst($key) }}</strong>
                <p>{{ $usage[$key] ?? 0 }} / {{ $limit }}</p>
            </div>
        @endforeach
    </div>
</div>

<div class="card">
    <h2>Subscrição</h2>

    <form method="POST" action="/subscription/change">
        @csrf

        <select name="plan_id">
            @foreach(\App\Models\Plan::where('active', true)->get() as $plan)
                <option value="{{ $plan->id }}">
                    {{ $plan->name }} — {{ $plan->price }}€
                </option>
            @endforeach
        </select>

        <button>Alterar plano</button>
    </form>
</div>

@endsection
