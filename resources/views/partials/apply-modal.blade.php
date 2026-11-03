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
                <button class="choice choice-go" type="button" data-flow="online">
                    <small>En línea</small>
                    <strong>Subo mis documentos ahora</strong>
                    <span>Confirmas la lista, creas cuenta si hace falta y subes fotos. Queda pendiente de aprobación.</span>
                </button>
                <button class="choice choice-alt" type="button" data-flow="persona">
                    <small>En la sede</small>
                    <strong>Llevo mis documentos</strong>
                    <span>Solo ves la lista de recaudos. Sin cuenta ni formulario. Llevas la carpeta en persona.</span>
                </button>
            </div>
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
    const rate = Number(@json($usdVesRate ?? 1000));
    const money = (value) => {
        const whole = (n) => Math.round(Number(n)).toLocaleString('es-VE', { maximumFractionDigits: 0 });
        const usd = '$' + whole(value) + ' USD';
        const ves = 'Bs. ' + whole(Number(value) * rate);
        return usd + '<br><span class="money-ves">' + ves + '</span>';
    };
    const photo = document.getElementById('apply-photo');
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
    }

    function step(direction) {
        if (!current) return;
        const index = motos.findIndex((moto) => moto.slug === current.slug);
        const next = motos[(index + direction + motos.length) % motos.length];
        paint(next.slug, direction);
    }

    function openApply(slug) {
        paint(slug, 1);
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

    dialog.querySelectorAll('[data-flow]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!current) return;
            dialog.close();
            if (button.dataset.flow === 'persona' && typeof window.openPersonaDocs === 'function') {
                window.openPersonaDocs(current.slug);
            } else if (typeof window.openOnlineDocs === 'function') {
                window.openOnlineDocs(current.slug, 'checklist');
            }
        });
    });

    const bootSlug = @json(request('solicitar') ?: ($oldMoto->slug ?? ''));
    const bootCanal = @json(request('canal'));
    const bootPaso = @json(request('paso'));
    // Online resume is handled by docs-modal; only open chooser if no paso.
    if (bootSlug && !bootPaso && !bootCanal) openApply(bootSlug);
})();
</script>
