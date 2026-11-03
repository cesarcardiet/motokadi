@extends('layouts.app')
@section('title', 'Tu moto con financiamiento')
@section('content')
    <section class="calc-band bleed" id="calculadora">
        <div class="calc-shell">
            <div class="calc-copy">
                <p class="kicker">Calculadora</p>
                <h2>Mira la cuota antes de pedirla</h2>

                <div class="calc-model-row">
                    <button class="nav-arrow" id="calc-prev" type="button" aria-label="Modelo anterior">‹</button>
                    <div class="calc-model-field">
                        <label for="calc-model">Modelo</label>
                        <select id="calc-model"></select>
                    </div>
                    <button class="nav-arrow" id="calc-next" type="button" aria-label="Modelo siguiente">›</button>
                </div>

                <div class="calc-monthly-row">
                    <button class="calc-thumb" type="button" id="calc-thumb" aria-label="Ver moto" hidden>
                        <img id="calc-thumb-img" alt="">
                    </button>
                    <p class="calc-monthly">
                        <span>Cuota mensual</span>
                        <strong id="calc-monthly"></strong>
                    </p>
                </div>

                <div class="quote-box calc-quote" id="calc-quote"></div>

                <p class="muted calc-rate">Tasa vigente: {{ \App\Support\Money::rate() }}</p>

                <button class="btn calc-cta" type="button" id="calc-apply">Solicitar esta moto</button>
            </div>
        </div>
    </section>

    <dialog class="calc-bike-dialog" id="calc-bike-dialog">
        <form method="dialog" class="calc-bike-panel">
            <button class="close-x" type="submit" aria-label="Cerrar">×</button>
            <p class="kicker" id="calc-bike-kicker"></p>
            <h2 id="calc-bike-title"></h2>
            <div class="calc-bike-photo" id="calc-bike-photo"></div>
            <p class="muted" id="calc-bike-summary"></p>
        </form>
    </dialog>

    <section class="section">
        <div class="steps">
            <article class="step"><b>01</b><h3>Elige la moto</h3><p class="muted">Abres la ficha y ves precio, inicial y cuota antes de empezar.</p></article>
            <article class="step"><b>02</b><h3>Documentos o carpeta</h3><p class="muted">Subes cédula, RIF, licencia y constancia, o llevas esa carpeta a la sede.</p></article>
            <article class="step"><b>03</b><h3>Ubicación y cierre</h3><p class="muted">Después de los papeles se verifica el GPS. Eso preaprueba. Administración da el sí final.</p></article>
        </div>
    </section>

    <section class="section">
        <div class="section-head">
            <h2>Todos los modelos</h2>
            <a href="{{ route('catalog.index') }}">Abrir el catálogo</a>
        </div>
        <div class="bike-grid">
            @foreach ($featured as $motorcycle)
                <x-moto-card :motorcycle="$motorcycle" />
            @endforeach
        </div>
    </section>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const data = document.getElementById('apply-data');
    if (!data) return;
    const motos = JSON.parse(data.textContent);
    const rate = Number(@json($usdVesRate ?? 1000));
    const whole = (n) => Math.round(Number(n)).toLocaleString('es-VE', { maximumFractionDigits: 0 });
    const money = (value) => {
        const usd = '$' + whole(value) + ' USD';
        const ves = 'Bs. ' + whole(Number(value) * rate);
        return `<span class="money-usd">${usd}</span><span class="money-ves">${ves}</span>`;
    };
    const modelSelect = document.getElementById('calc-model');
    const thumbBtn = document.getElementById('calc-thumb');
    const thumbImg = document.getElementById('calc-thumb-img');
    const bikeDialog = document.getElementById('calc-bike-dialog');
    let slug = motos[0]?.slug;
    let current = null;

    function paint(nextSlug) {
        const list = motos;
        const moto = list.find((item) => item.slug === nextSlug) || list[0];
        if (!moto) return;
        current = moto;
        slug = moto.slug;
        modelSelect.value = moto.slug;
        const quote = moto.quote;
        const monthly = document.getElementById('calc-monthly');
        monthly.innerHTML = money(quote.monthly);
        monthly.classList.remove('pop');
        void monthly.offsetWidth;
        monthly.classList.add('pop');
        document.getElementById('calc-quote').innerHTML = `
            <div><span class="muted">Precio</span><strong>${money(quote.price)}</strong></div>
            <div><span class="muted">Inicial</span><strong>${money(quote.initial)}</strong></div>
            <div><span class="muted">Saldo</span><strong>${money(quote.balance)}</strong></div>
            <div><span class="muted">9 meses</span><strong>${money(quote.monthly)}</strong></div>`;

        if (moto.photo) {
            thumbImg.src = moto.photo;
            thumbImg.alt = moto.name;
            thumbBtn.hidden = false;
        } else {
            thumbBtn.hidden = true;
            thumbImg.removeAttribute('src');
        }
    }

    function fillModels() {
        modelSelect.replaceChildren(...motos.map((moto) => {
            const option = document.createElement('option');
            option.value = moto.slug;
            option.textContent = moto.short + ' · ' + moto.cc + ' cc';
            return option;
        }));
        if (!motos.some((moto) => moto.slug === slug)) slug = motos[0]?.slug;
        paint(slug);
    }

    modelSelect.addEventListener('change', () => paint(modelSelect.value));
    document.getElementById('calc-prev').addEventListener('click', () => {
        const index = motos.findIndex((moto) => moto.slug === slug);
        paint(motos[(index - 1 + motos.length) % motos.length].slug);
    });
    document.getElementById('calc-next').addEventListener('click', () => {
        const index = motos.findIndex((moto) => moto.slug === slug);
        paint(motos[(index + 1) % motos.length].slug);
    });
    document.getElementById('calc-apply').addEventListener('click', () => {
        if (window.openApply) window.openApply(slug);
    });
    thumbBtn.addEventListener('click', () => {
        if (!current || !bikeDialog) return;
        document.getElementById('calc-bike-kicker').textContent = (current.line || '') + ' · ' + current.cc + ' cc';
        document.getElementById('calc-bike-title').textContent = current.name || current.short;
        document.getElementById('calc-bike-summary').textContent = current.summary || '';
        const photo = document.getElementById('calc-bike-photo');
        photo.innerHTML = current.photo
            ? `<img src="${current.photo}" alt="${current.name || ''}">`
            : `<div class="photo-missing">Foto pendiente</div>`;
        if (typeof bikeDialog.showModal === 'function') bikeDialog.showModal();
    });
    fillModels();
});
</script>
@endsection
