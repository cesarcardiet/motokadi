@php
    $applyPayload = $applyMotorcycles->map(function ($moto) {
        $quote = $moto->quote();
        return [
            'id' => $moto->id,
            'slug' => $moto->slug,
            'name' => $moto->name,
            'short' => str_replace('Kadi ', '', $moto->name),
            'summary' => $moto->summary,
            'colors' => $moto->colors,
            'photo' => $moto->photoUrl(),
            'cc' => $moto->displacement,
            'line' => $moto->lineLabel(),
            'quote' => $quote,
        ];
    })->values();
    $oldMoto = $applyMotorcycles->firstWhere('id', (int) old('motorcycle_id'));
@endphp
<dialog id="apply-dialog" class="apply-dialog">
    <button class="close-x" type="button" data-close-apply aria-label="Cerrar">×</button>
    <span class="banner-mark banner-mark-tl"></span>
    <span class="banner-mark banner-mark-br"></span>
    <div class="apply-shell">
        <div class="apply-main">
            <p class="apply-count" id="apply-count"></p>
            <p class="kicker" id="apply-kicker">Financiamiento</p>
            <h2 id="apply-title">Elige tu moto</h2>
            <p id="apply-summary" class="muted"></p>
            <div class="quote-box" id="apply-quote"></div>
            <div id="apply-choices" class="choice-grid">
                <button class="choice choice-go" type="button" data-channel="en_linea">
                    <small>En línea</small>
                    <strong>Subir documentos</strong>
                    <span>Cargas cédula, RIF, licencia y constancia. Te llamamos para la preevaluación.</span>
                </button>
                <button class="choice choice-alt" type="button" data-channel="en_sede">
                    <small>En la sede</small>
                    <strong>Llevar la carpeta</strong>
                    <span>Llevas los mismos requisitos a la sede. Allí revisan la carpeta.</span>
                </button>
            </div>
            <form id="apply-form" method="POST" action="{{ route('client.applications.store') }}" enctype="multipart/form-data" hidden>
                @csrf
                <input type="hidden" name="motorcycle_id" id="apply-moto-id">
                <input type="hidden" name="channel" id="apply-channel" value="{{ old('channel', 'en_linea') }}">
                <div class="form-grid">
                    <div>
                        <label for="apply-color">Color</label>
                        <select id="apply-color" name="color" required></select>
                    </div>
                    <div>
                        <label for="apply-branch">Sede</label>
                        <select id="apply-branch" name="branch_id" required>
                            @foreach ($applyBranches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="apply-city">Ciudad</label>
                        <input id="apply-city" name="city" value="{{ old('city') }}" required>
                    </div>
                    <div id="apply-address-wrap">
                        <label for="apply-address">Dirección</label>
                        <input id="apply-address" name="address" value="{{ old('address') }}">
                    </div>
                    <div id="apply-lat-wrap">
                        <label for="apply-lat">Latitud GPS</label>
                        <input id="apply-lat" name="latitude" value="{{ old('latitude') }}">
                    </div>
                    <div id="apply-lng-wrap">
                        <label for="apply-lng">Longitud GPS</label>
                        <input id="apply-lng" name="longitude" value="{{ old('longitude') }}">
                    </div>
                    <div class="full" id="apply-geo-wrap">
                        <button class="btn ghost small" type="button" id="apply-geo">Usar mi ubicación</button>
                    </div>
                    <div class="full" id="apply-docs">
                        @foreach (\App\Enums\DocumentType::required() as $type)
                            <label for="apply-doc-{{ $type->value }}">{{ $type->label() }}</label>
                            <input id="apply-doc-{{ $type->value }}" type="file" name="documents[{{ $type->value }}]" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        @endforeach
                    </div>
                </div>
                @if ($errors->any())
                    <div class="flash error">{{ $errors->first() }}</div>
                @endif
                <div class="actions">
                    <button class="btn ghost" type="button" id="apply-back">Volver</button>
                    <button class="btn" type="submit" id="apply-submit">Enviar solicitud</button>
                </div>
                @guest
                    <p class="muted" id="apply-guest-note">Para enviar tienes que entrar. El botón te lleva al ingreso y regresas a esta moto.</p>
                @endguest
            </form>
        </div>
        <div class="apply-stage">
            <button class="nav-arrow" id="apply-prev" type="button" aria-label="Modelo anterior">‹</button>
            <div class="photo-stage" id="apply-photo"></div>
            <button class="nav-arrow" id="apply-next" type="button" aria-label="Modelo siguiente">›</button>
        </div>
    </div>
</dialog>
<script type="application/json" id="apply-data">{!! $applyPayload->toJson(JSON_UNESCAPED_UNICODE) !!}</script>
<script>
(() => {
    const dialog = document.getElementById('apply-dialog');
    if (!dialog || dialog.dataset.ready) return;
    dialog.dataset.ready = '1';
    const motos = JSON.parse(document.getElementById('apply-data').textContent);
    const money = (value) => '$' + Number(value).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' USD';
    const form = document.getElementById('apply-form');
    const choices = document.getElementById('apply-choices');
    const photo = document.getElementById('apply-photo');
    const authed = {{ auth()->check() ? 'true' : 'false' }};
    let current = null;

    function paint(slug, direction) {
        current = motos.find((moto) => moto.slug === slug) || motos[0];
        if (!current) return;
        const index = motos.findIndex((moto) => moto.slug === current.slug);
        document.getElementById('apply-title').textContent = current.short;
        document.getElementById('apply-kicker').textContent = current.line + ' · ' + current.cc + ' cc';
        document.getElementById('apply-summary').textContent = current.summary;
        document.getElementById('apply-count').textContent = (index + 1) + ' / ' + motos.length;
        photo.classList.remove('from-left', 'from-right');
        void photo.offsetWidth;
        photo.classList.add(direction === -1 ? 'from-left' : 'from-right');
        photo.innerHTML = current.photo
            ? `<img src="${current.photo}" alt="${current.name}">`
            : `<div class="photo-missing">Foto pendiente</div>`;
        const quote = current.quote;
        document.getElementById('apply-quote').innerHTML = `
            <div><span class="muted">Precio</span><strong>${money(quote.price)}</strong></div>
            <div><span class="muted">Inicial</span><strong>${money(quote.initial)}</strong></div>
            <div><span class="muted">Saldo</span><strong>${money(quote.balance)}</strong></div>
            <div><span class="muted">Cuota</span><strong>${money(quote.monthly)}</strong></div>`;
        document.getElementById('apply-moto-id').value = current.id;
        const color = document.getElementById('apply-color');
        const previous = color.value;
        color.replaceChildren(...current.colors.map((item) => {
            const option = document.createElement('option');
            option.value = item;
            option.textContent = item;
            return option;
        }));
        if (current.colors.includes(previous)) color.value = previous;
        else if (current.colors[0]) color.value = current.colors[0];
    }

    function step(direction) {
        if (!current) return;
        const index = motos.findIndex((moto) => moto.slug === current.slug);
        const next = motos[(index + direction + motos.length) % motos.length];
        paint(next.slug, direction);
    }

    function setChannel(channel) {
        document.getElementById('apply-channel').value = channel;
        const online = channel === 'en_linea';
        document.getElementById('apply-docs').hidden = !online;
        document.getElementById('apply-lat-wrap').hidden = !online;
        document.getElementById('apply-lng-wrap').hidden = !online;
        document.getElementById('apply-geo-wrap').hidden = !online;
        document.getElementById('apply-address').required = online;
        document.getElementById('apply-lat').required = online;
        document.getElementById('apply-lng').required = online;
        document.querySelectorAll('#apply-docs input').forEach((input) => { input.required = online; });
        document.getElementById('apply-submit').textContent = online ? 'Enviar y esperar la llamada' : 'Confirmar visita con la carpeta';
        choices.hidden = true;
        form.hidden = false;
    }

    function openApply(slug, channel) {
        paint(slug, 1);
        choices.hidden = false;
        form.hidden = true;
        if (channel && authed) setChannel(channel);
        if (typeof dialog.showModal === 'function' && !dialog.open) dialog.showModal();
    }
    window.openApply = openApply;

    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-open-moto]');
        if (opener) {
            event.preventDefault();
            openApply(opener.dataset.openMoto);
        }
    });
    dialog.querySelector('[data-close-apply]').addEventListener('click', () => dialog.close());
    document.getElementById('apply-prev').addEventListener('click', () => step(-1));
    document.getElementById('apply-next').addEventListener('click', () => step(1));
    choices.querySelectorAll('[data-channel]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!authed) {
                const next = '/catalogo?solicitar=' + encodeURIComponent(current.slug) + '&canal=' + button.dataset.channel;
                window.location = '/ingresar?next=' + encodeURIComponent(next);
                return;
            }
            setChannel(button.dataset.channel);
        });
    });
    document.getElementById('apply-back').addEventListener('click', () => {
        form.hidden = true;
        choices.hidden = false;
    });
    document.getElementById('apply-geo').addEventListener('click', () => {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition((position) => {
            document.getElementById('apply-lat').value = position.coords.latitude.toFixed(6);
            document.getElementById('apply-lng').value = position.coords.longitude.toFixed(6);
        });
    });

    const bootSlug = @json(request('solicitar') ?: ($oldMoto->slug ?? ''));
    const bootChannel = @json(old('channel', request('canal')));
    const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
    if (bootSlug) openApply(bootSlug, authed && (hasErrors || bootChannel) ? bootChannel : null);
})();
</script>
