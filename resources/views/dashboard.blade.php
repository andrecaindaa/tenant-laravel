@extends('layouts.app')

@section('title','Dashboard')

@section('content')

<div class="card">
    <h2>Plano atual</h2>
    @if($subscription)
        <strong>{{ $subscription->plan->name }}</strong>
    @else
        <p>Sem subscrição</p>
    @endif
</div>

<div class="card">
    <h2>Limites</h2>
    @foreach($limits as $key => $limit)
        <p>{{ ucfirst($key) }}: {{ $usage[$key] ?? 0 }} / {{ $limit }}</p>
    @endforeach
</div>

<div class="card">
    <h2>Alterar plano</h2>
    <form method="POST" action="/subscription/change">
        @csrf
        <select name="plan_id">
            @foreach(\App\Models\Plan::where('active',true)->get() as $plan)
                <option value="{{ $plan->id }}">
                    {{ $plan->name }} — {{ $plan->price }}€
                </option>
            @endforeach
        </select>
        <button>Alterar</button>
    </form>
</div>

<a href="/plans">Gerir planos</a>

@endsection
