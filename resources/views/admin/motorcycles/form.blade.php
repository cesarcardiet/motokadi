@extends('layouts.app')
@section('title', $motorcycle->exists ? 'Editar modelo' : 'Nuevo modelo')
@section('content')
    <form class="auth-card" method="POST" action="{{ $motorcycle->exists ? route('admin.motorcycles.update', $motorcycle) : route('admin.motorcycles.store') }}">
        @csrf
        @if ($motorcycle->exists) @method('PUT') @endif
        <h2>{{ $motorcycle->exists ? 'Editar '.$motorcycle->name : 'Nuevo modelo' }}</h2>
        <div class="form-grid">
            <div><label for="name">Nombre</label><input id="name" name="name" value="{{ old('name', $motorcycle->name) }}" required></div>
            <div><label for="slug">Identificador</label><input id="slug" name="slug" value="{{ old('slug', $motorcycle->slug) }}" required></div>
            <div>
                <label for="line">Línea</label>
                <select id="line" name="line">
                    @foreach (['enduro' => 'Enduro', 'atv' => 'Cuatrimoto', 'sincronica' => 'Sincrónica', 'automatica' => 'Automática', 'carguero' => 'Carguero'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('line', $motorcycle->line) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div><label for="displacement">Cilindrada</label><input id="displacement" name="displacement" type="number" value="{{ old('displacement', $motorcycle->displacement) }}" required></div>
            <div class="full"><label for="summary">Descripción</label><input id="summary" name="summary" value="{{ old('summary', $motorcycle->summary) }}" required></div>
            <div><label for="price">Precio USD</label><input id="price" name="price" type="number" step="0.01" value="{{ old('price', $motorcycle->price) }}" required>@error('price')<div class="help">{{ $message }}</div>@enderror</div>
            <div><label for="initial_payment">Inicial USD</label><input id="initial_payment" name="initial_payment" type="number" step="0.01" value="{{ old('initial_payment', $motorcycle->initial_payment) }}" required>@error('initial_payment')<div class="help">{{ $message }}</div>@enderror</div>
            <div><label for="term_months">Meses</label><input id="term_months" name="term_months" type="number" value="{{ old('term_months', $motorcycle->term_months ?: 9) }}" required></div>
            <div><label for="colors">Colores, separados por coma</label><input id="colors" name="colors" value="{{ old('colors', implode(', ', $motorcycle->colors ?? [])) }}" required></div>
            <label class="check full"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $motorcycle->is_active) == true)> Visible en el catálogo</label>
        </div>
        <p class="muted">La cuota se recalcula al guardar. Las solicitudes ya enviadas no cambian.</p>
        <div class="actions"><button class="btn" type="submit">Guardar</button></div>
    </form>
@endsection
