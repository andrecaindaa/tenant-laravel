@extends('layouts.app')

@section('title','Planos')

@section('content')

<div class="card">
    <h2>Criar plano</h2>

    <form method="POST">
        @csrf

        <input name="name" placeholder="Nome do plano" required>
        <input name="price" placeholder="Preço" required>

        <h4>Limites</h4>
        <input name="limits[users]" placeholder="Users">
        <input name="limits[projects]" placeholder="Projects">

        <button>Criar plano</button>
    </form>
</div>

<div class="card">
    <h2>Planos existentes</h2>

   @foreach($plans as $plan)
    <p>
        <strong>{{ $plan->name }}</strong>
        ({{ $plan->slug }})
        — {{ $plan->price }}€
        — {{ json_encode($plan->limits) }}
    </p>
@endforeach

</div>

@endsection
