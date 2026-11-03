@extends('layouts.app')
@section('title', 'Motos y cuotas')
@section('content')
    <section class="hero">
        <div class="split">
            <div>
                <p class="kicker">Catálogo</p>
                <h1>Motos y cuotas</h1>
            </div>
            <a class="btn" href="{{ route('admin.motorcycles.create') }}">Nuevo modelo</a>
        </div>
        <x-quote-table :motorcycles="$motorcycles" />
        <div class="table-wrap" style="margin-top:16px">
            <table>
                <thead><tr><th>Modelo</th><th>Origen del precio</th><th></th></tr></thead>
                <tbody>
                    @foreach ($motorcycles as $motorcycle)
                        <tr>
                            <td>{{ $motorcycle->name }}</td>
                            <td>{{ $motorcycle->price_source === 'ejemplo' ? 'Tabla de ejemplo' : 'Estimado' }}</td>
                            <td><a href="{{ route('admin.motorcycles.edit', $motorcycle) }}">Editar precios</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $motorcycles->links('partials.pager') }}
    </section>
@endsection
