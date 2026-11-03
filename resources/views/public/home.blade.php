@extends('layouts.app')
@section('title', 'Tu moto con financiamiento')
@section('content')
    <section class="calc-band bleed" id="calculadora">
        <div class="calc-copy">
            <p class="kicker">Calculadora</p>
            <h2>Mira la cuota antes de pedirla</h2>
            <label for="calc-model">Modelo</label>
            <select id="calc-model"></select>
            <p class="calc-monthly"><span>Cuota mensual</span><strong id="calc-monthly"></strong></p>
            <div class="quote-box" id="calc-quote"></div>
            <button class="btn calc-cta" type="button" id="calc-apply">Solicitar esta moto</button>
        </div>
        <div class="calc-visual">
            <button class="nav-arrow" id="calc-prev" type="button" aria-label="Modelo anterior">‹</button>
            <div class="calc-photo" id="calc-photo"></div>
            <button class="nav-arrow" id="calc-next" type="button" aria-label="Modelo siguiente">›</button>
        </div>
    </section>

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
    const money = (value) => '$' + Number(value).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' USD';
    const modelSelect = document.getElementById('calc-model');
    const photo = document.getElementById('calc-photo');
    let slug = motos[0]?.slug;

    function pool() {
        return motos;
    }

    function paint(nextSlug, direction) {
        const list = pool();
        const moto = list.find((item) => item.slug === nextSlug) || list[0];
        if (!moto) return;
        slug = moto.slug;
        modelSelect.value = moto.slug;
        photo.classList.remove('from-left', 'from-right');
        void photo.offsetWidth;
        photo.classList.add(direction < 0 ? 'from-left' : 'from-right');
        photo.innerHTML = moto.photo
            ? `<img src="${moto.photo}" alt="${moto.name}">`
            : `<div class="photo-missing">Foto pendiente</div>`;
        const quote = moto.quote;
        const monthly = document.getElementById('calc-monthly');
        monthly.textContent = money(quote.monthly);
        monthly.classList.remove('pop');
        void monthly.offsetWidth;
        monthly.classList.add('pop');
        document.getElementById('calc-quote').innerHTML = `
            <div><span class="muted">Precio</span><strong>${money(quote.price)}</strong></div>
            <div><span class="muted">Inicial</span><strong>${money(quote.initial)}</strong></div>
            <div><span class="muted">Saldo</span><strong>${money(quote.balance)}</strong></div>
            <div><span class="muted">9 meses</span><strong>${money(quote.monthly)}</strong></div>`;
    }

    function fillModels() {
        const list = pool();
        modelSelect.replaceChildren(...list.map((moto) => {
            const option = document.createElement('option');
            option.value = moto.slug;
            option.textContent = moto.short + ' · ' + moto.cc + ' cc';
            return option;
        }));
        if (!list.some((moto) => moto.slug === slug)) slug = list[0]?.slug;
        paint(slug, 1);
    }

    modelSelect.addEventListener('change', () => paint(modelSelect.value, 1));
    document.getElementById('calc-prev').addEventListener('click', () => {
        const list = pool();
        const index = list.findIndex((moto) => moto.slug === slug);
        paint(list[(index - 1 + list.length) % list.length].slug, -1);
    });
    document.getElementById('calc-next').addEventListener('click', () => {
        const list = pool();
        const index = list.findIndex((moto) => moto.slug === slug);
        paint(list[(index + 1) % list.length].slug, 1);
    });
    document.getElementById('calc-apply').addEventListener('click', () => {
        if (window.openApply) window.openApply(slug);
    });
    fillModels();
});
</script>
@endsection
