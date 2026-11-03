@extends('layouts.app')
@section('title', 'Nueva solicitud')
@section('content')
    <section class="hero">
        <p class="kicker">Solicitud</p>
        <h1>Ingresar recaudos</h1>
        <p class="lead">Los cuatro documentos y el GPS son obligatorios. Elige si la primera revisión es en línea o si llevas todo a la sede.</p>
        <form class="panel" method="POST" action="{{ route('client.applications.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
                <div>
                    <label for="motorcycle_id">Modelo</label>
                    <select id="motorcycle_id" name="motorcycle_id" required>
                        @foreach ($motorcycles as $motorcycle)
                            <option value="{{ $motorcycle->id }}" @selected((string) old('motorcycle_id', $selectedId) === (string) $motorcycle->id)>
                                {{ $motorcycle->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('motorcycle_id')<div class="help">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="color">Color</label>
                    <select id="color" name="color" required></select>
                    @error('color')<div class="help">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="branch_id">Sede</label>
                    <select id="branch_id" name="branch_id" required>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>{{ $branch->name }} · {{ $branch->city }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="channel">Cómo verificas</label>
                    <select id="channel" name="channel" required>
                        <option value="en_linea" @selected(old('channel') === 'en_linea')>Preevaluación en línea</option>
                        <option value="en_sede" @selected(old('channel') === 'en_sede')>Llevo los papeles a la sede</option>
                    </select>
                </div>
                <div class="full" id="quote-preview"></div>
                <div>
                    <label for="city">Ciudad</label>
                    <input id="city" name="city" value="{{ old('city') }}" required>
                    @error('city')<div class="help">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="address">Dirección</label>
                    <input id="address" name="address" value="{{ old('address') }}" required>
                    @error('address')<div class="help">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="latitude">Latitud GPS</label>
                    <input id="latitude" name="latitude" value="{{ old('latitude') }}" required>
                    @error('latitude')<div class="help">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="longitude">Longitud GPS</label>
                    <input id="longitude" name="longitude" value="{{ old('longitude') }}" required>
                    @error('longitude')<div class="help">{{ $message }}</div>@enderror
                </div>
                <div class="full">
                    <button class="btn ghost" type="button" id="geo">Usar mi ubicación</button>
                </div>
                @foreach ($documentTypes as $type)
                    <div class="full">
                        <label for="doc-{{ $type->value }}">{{ $type->label() }}</label>
                        <input id="doc-{{ $type->value }}" type="file" name="documents[{{ $type->value }}]" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                        @error('documents.'.$type->value)<div class="help">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
            <div class="actions"><button class="btn" type="submit">Enviar solicitud</button></div>
        </form>
    </section>
    <script type="application/json" id="moto-data">{!! $motorcycles->map(fn ($moto) => ['id' => $moto->id, 'colors' => $moto->colors, 'quote' => $moto->quote()])->values()->toJson() !!}</script>
    <script>
        const motos = JSON.parse(document.getElementById('moto-data').textContent);
        const select = document.getElementById('motorcycle_id');
        const color = document.getElementById('color');
        const preview = document.getElementById('quote-preview');
        const money = (value) => '$' + Number(value).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' USD';
        function paint() {
            const moto = motos.find((item) => String(item.id) === select.value);
            if (!moto) return;
            const previous = color.value;
            color.innerHTML = moto.colors.map((item) => `<option value="${item}">${item}</option>`).join('');
            if (moto.colors.includes(previous)) color.value = previous;
            const quote = moto.quote;
            preview.innerHTML = `<div class="quote-box"><div><span class="muted">Precio</span><strong>${money(quote.price)}</strong></div><div><span class="muted">Inicial</span><strong>${money(quote.initial)}</strong></div><div><span class="muted">Saldo</span><strong>${money(quote.balance)}</strong></div><div><span class="muted">Cuota</span><strong>${money(quote.monthly)}</strong></div></div>`;
        }
        select.addEventListener('change', paint);
        paint();
        document.getElementById('geo').addEventListener('click', () => {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition((position) => {
                document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
                document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
            });
        });
    </script>
@endsection
