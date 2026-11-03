@php
    $docTypes = \App\Enums\DocumentType::required();
    $googleMapsKey = config('services.google_maps.key');
    $canUpload = auth()->check() && auth()->user()->hasPermission('solicitudes.crear');
    $defaultWhatsapp = old('whatsapp_phone', auth()->user()->phone ?? '');
    $activeApplication = $canUpload ? auth()->user()->activeApplication() : null;
@endphp
<dialog id="persona-docs-dialog" class="docs-dialog" aria-labelledby="persona-docs-title">
    <button class="close-x" type="button" data-close-persona aria-label="Cerrar">×</button>
    <div class="docs-shell">
        <p class="kicker">En la sede</p>
        <h2 id="persona-docs-title">Llevar en persona</h2>
        <p class="docs-lead muted">Arma esta carpeta y llévala a la sede. No necesitas cuenta ni subir nada aquí.</p>
        <ol class="docs-checklist">
            @foreach ($docTypes as $index => $type)
                <li>
                    <b>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</b>
                    <div>
                        <strong>{{ $type->label() }}</strong>
                        <span class="muted">Copia física o impresa</span>
                    </div>
                </li>
            @endforeach
            <li>
                <b>{{ str_pad((string) (count($docTypes) + 1), 2, '0', STR_PAD_LEFT) }}</b>
                <div>
                    <strong>Ubicación</strong>
                    <span class="muted">En la sede confirman tu dirección / GPS al recibir la carpeta.</span>
                </div>
            </li>
        </ol>
        <div class="docs-note">
            <strong>Sin cuenta</strong>
            <p class="muted">Esto es solo la lista. Cuando llegues, el vendedor recibe la carpeta y sigue el proceso.</p>
        </div>
        <div class="docs-actions">
            <button class="btn" type="button" data-close-persona>Cerrar</button>
        </div>
    </div>
</dialog>

<dialog id="online-docs-dialog" class="docs-dialog docs-dialog-wide" aria-labelledby="online-docs-title">
    <button class="close-x" type="button" data-close-online aria-label="Cerrar">×</button>
    <div class="docs-shell">
        <div class="flow-progress" id="online-progress" hidden>
            <div class="flow-track"><div class="flow-fill" id="online-progress-fill"></div></div>
            <div class="step-indicator" id="online-step-indicator">
                <button type="button" class="step-pill is-on" data-pill="1" disabled>1 · Datos</button>
                <button type="button" class="step-pill" data-pill="2" disabled>2 · Ubicación</button>
                <button type="button" class="step-pill" data-pill="3" disabled>3 · Docs</button>
                <button type="button" class="step-pill" data-pill="4" disabled>4 · Revisar</button>
            </div>
        </div>

        <p class="kicker" id="online-kicker">En línea</p>
        <h2 id="online-docs-title">Subir documentos</h2>
        <p class="docs-lead muted" id="online-lead">Cuatro pasos: datos, ubicación, documentos y confirmación.</p>

        <div id="online-step-auth" hidden>
            <div class="docs-note">
                <strong>Necesitas una cuenta</strong>
                <p class="muted">Para subir archivos e indicar tu ubicación, crea una cuenta o inicia sesión. Volverás al formulario.</p>
            </div>
            <div class="docs-actions">
                <button class="btn ghost" type="button" data-close-online>Cerrar</button>
                <a class="btn ghost" id="online-login" href="{{ route('login') }}">Iniciar sesión</a>
                <a class="btn" id="online-register" href="{{ route('register') }}">Crear cuenta</a>
            </div>
        </div>

        <div id="online-step-blocked" hidden>
            <div class="docs-note">
                <strong>Tienes una solicitud activa, espera la aprobación</strong>
                <p class="muted">
                    No puedes enviar otra solicitud hasta que un administrador apruebe o rechace la actual
                    @if ($activeApplication)
                        ({{ $activeApplication->status->label() }}).
                    @else
                        .
                    @endif
                </p>
            </div>
            <div class="docs-actions">
                <button class="btn ghost" type="button" data-close-online>Cerrar</button>
                @if ($activeApplication)
                    <a class="btn" href="{{ route('client.applications.show', $activeApplication) }}">Ver mi solicitud</a>
                @else
                    <a class="btn" href="{{ route('client.applications.index') }}">Mis solicitudes</a>
                @endif
            </div>
        </div>

        @if ($canUpload && ! $activeApplication)
            <form id="online-upload-form" method="POST" action="{{ route('client.applications.store') }}" enctype="multipart/form-data" hidden>
                @csrf
                <input type="hidden" name="motorcycle_id" id="online-moto-id">
                <input type="hidden" name="channel" value="en_linea">

                {{-- Paso 1 --}}
                <div id="online-step-1" class="online-panel">
                    <div class="form-grid dark-form">
                        <div class="full">
                            <label for="online-moto-label">Modelo</label>
                            <input id="online-moto-label" type="text" readonly>
                        </div>
                        <div>
                            <label for="online-color">Color</label>
                            <select id="online-color" name="color" required></select>
                        </div>
                        <div>
                            <label for="online-branch">Sede</label>
                            <select id="online-branch" name="branch_id" required>
                                @foreach ($applyBranches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }} · {{ $branch->city }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="online-city">Ciudad</label>
                            <input id="online-city" name="city" value="{{ old('city') }}" required minlength="2" maxlength="80" placeholder="Ej. Naguanagua">
                        </div>
                        <div class="full">
                            <label for="online-address">Dirección</label>
                            <input id="online-address" name="address" value="{{ old('address') }}" required minlength="5" maxlength="500" placeholder="Calle, urbanización, casa/apto">
                        </div>
                        <div class="full">
                            <label for="online-whatsapp">WhatsApp / teléfono</label>
                            <input id="online-whatsapp" name="whatsapp_phone" value="{{ $defaultWhatsapp }}" required minlength="10" maxlength="40" inputmode="tel" placeholder="Ej. +58 412 0000000" pattern="[0-9+() -]{10,40}" autocomplete="tel">
                            <small class="field-hint">Lo usamos para avisos y contacto de la solicitud.</small>
                        </div>
                    </div>
                    <p class="help" id="online-err-1" hidden>Completa todos los campos del paso 1.</p>
                    <div class="docs-actions">
                        <button class="btn ghost" type="button" data-close-online>Cerrar</button>
                        <button class="btn" type="button" data-next-step="2">Continuar</button>
                    </div>
                </div>

                {{-- Paso 2 --}}
                <div id="online-step-2" class="online-panel" hidden>
                    <section class="map-section" aria-label="Ubicación">
                        <p class="map-heading">Ubicación exacta</p>
                        <p class="muted map-hint">Busca tu dirección o permite el GPS del navegador. Puedes arrastrar el pin.</p>

                        <div id="online-geo-banner" class="geo-banner">
                            <strong>¿Quieres permitir que el navegador use tu ubicación?</strong>
                            <p class="muted">Si aceptas, movemos el pin y completamos dirección/coordenadas.</p>
                            <div class="geo-actions">
                                <button class="btn" type="button" id="online-geo-allow">Permitir ubicación</button>
                                <button class="btn ghost" type="button" id="online-geo-deny">Buscar a mano</button>
                            </div>
                            <p class="help" id="online-geo-error" hidden></p>
                        </div>

                        <label for="online-place-search">Buscar ubicación exacta</label>
                        <div class="place-wrap">
                            <input id="online-place-search" type="text" placeholder="Escribe calle, urbanización o referencia" autocomplete="off" role="combobox" aria-autocomplete="list" aria-controls="online-place-suggestions" aria-expanded="false">
                            <ul id="online-place-suggestions" class="place-suggestions" role="listbox" hidden></ul>
                        </div>
                        <div id="online-map" class="kadi-map" role="application" aria-label="Mapa de ubicación"></div>
                        <div class="form-grid dark-form map-fields">
                            <div class="coords-pair">
                                <div>
                                    <label for="online-lat">Latitud</label>
                                    <input id="online-lat" name="latitude" value="{{ old('latitude') }}" required readonly>
                                </div>
                                <div>
                                    <label for="online-lng">Longitud</label>
                                    <input id="online-lng" name="longitude" value="{{ old('longitude') }}" required readonly>
                                </div>
                            </div>
                        </div>
                        <button class="btn ghost small" type="button" id="online-geo">Pedir ubicación otra vez</button>
                        @unless ($googleMapsKey)
                            <p class="help">Falta GOOGLE_MAPS_API_KEY en el entorno.</p>
                        @endunless
                    </section>
                    <p class="help" id="online-err-2" hidden>Marca un punto en el mapa (latitud y longitud).</p>
                    <div class="docs-actions">
                        <button class="btn ghost" type="button" data-prev-step="1">Volver</button>
                        <button class="btn" type="button" data-next-step="3">Continuar</button>
                    </div>
                </div>

                {{-- Paso 3 --}}
                <div id="online-step-3" class="online-panel" hidden>
                    <p class="muted map-hint">Sube fotos o PDF claros (máx. 5 MB c/u).</p>
                    <div class="form-grid dark-form">
                        @foreach ($docTypes as $type)
                            <div class="full doc-upload-row">
                                <label for="online-doc-{{ $type->value }}">{{ $type->label() }}</label>
                                <input id="online-doc-{{ $type->value }}" type="file" name="documents[{{ $type->value }}]" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            </div>
                        @endforeach
                    </div>
                    <p class="help" id="online-err-3" hidden>Adjunta los 4 documentos requeridos.</p>
                    <div class="docs-actions">
                        <button class="btn ghost" type="button" data-prev-step="2">Volver</button>
                        <button class="btn" type="button" data-next-step="4">Revisar</button>
                    </div>
                </div>

                {{-- Paso 4 --}}
                <div id="online-step-4" class="online-panel" hidden>
                    <div class="review-card" id="online-review"></div>
                    @if ($errors->any())
                        <div class="flash error">{{ $errors->first() }}</div>
                    @endif
                    <div class="docs-actions">
                        <button class="btn ghost" type="button" data-prev-step="3">Volver</button>
                        <button class="btn" type="submit">Confirmar y enviar</button>
                    </div>
                </div>
            </form>
        @endif

        <div id="online-step-done" hidden>
            <div class="docs-note docs-note-ok">
                <strong>Solicitud enviada</strong>
                <p class="muted">Quedó <strong>pendiente de aprobación</strong>. Te contactaremos por WhatsApp. También puedes seguir en una sede.</p>
            </div>
            <div class="docs-actions">
                <a class="btn ghost" href="{{ route('requirements') }}">Ver sedes / requisitos</a>
                <a class="btn" id="online-done-link" href="{{ route('client.applications.index') }}">Ver mi solicitud</a>
            </div>
        </div>
    </div>
</dialog>

@if ($googleMapsKey)
<script>
window.__KADI_MAPS_KEY = @json($googleMapsKey);
window.__KADI_MAPS_READY = window.__KADI_MAPS_READY || new Promise((resolve) => {
    window.__kadiMapsResolve = resolve;
    if (window.google && window.google.maps) resolve();
});
function __kadiMapsBoot() {
    if (typeof window.__kadiMapsResolve === 'function') {
        window.__kadiMapsResolve();
        window.__kadiMapsResolve = null;
    }
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ urlencode($googleMapsKey) }}&callback=__kadiMapsBoot" async defer></script>
@endif

<script>
(() => {
    const persona = document.getElementById('persona-docs-dialog');
    const online = document.getElementById('online-docs-dialog');
    if (!persona || !online || online.dataset.ready) return;
    online.dataset.ready = '1';

    const authed = {{ $canUpload ? 'true' : 'false' }};
    const blocked = {{ $activeApplication ? 'true' : 'false' }};
    const motos = (() => {
        const el = document.getElementById('apply-data');
        return el ? JSON.parse(el.textContent) : [];
    })();

    let pendingSlug = '';
    let currentStep = 1;
    let map = null;
    let marker = null;
    let geocoder = null;
    let searchTimer = null;
    let searchSeq = 0;
    let reverseSeq = 0;
    const defaultCenter = { lat: 10.2442, lng: -67.9991 };
    const placesSearchUrl = @json(route('places.search'));
    const placesReverseUrl = @json(route('places.reverse'));

    function motoBySlug(slug) {
        return motos.find((m) => m.slug === slug) || motos[0] || null;
    }

    function nextUploadPath() {
        if (!pendingSlug) return '/catalogo?canal=en_linea&paso=upload';
        return '/catalogo?solicitar=' + encodeURIComponent(pendingSlug) + '&canal=en_linea&paso=upload';
    }

    function syncAuthLinks() {
        const next = nextUploadPath();
        const login = document.getElementById('online-login');
        const register = document.getElementById('online-register');
        if (login) login.href = '/ingresar?next=' + encodeURIComponent(next);
        if (register) register.href = '/registro?next=' + encodeURIComponent(next);
    }

    function setCoords(lat, lng) {
        const latEl = document.getElementById('online-lat');
        const lngEl = document.getElementById('online-lng');
        if (latEl) latEl.value = Number(lat).toFixed(6);
        if (lngEl) lngEl.value = Number(lng).toFixed(6);
    }

    function cityFromComponents(components) {
        if (!components) return '';
        const pick = (...types) => {
            for (const type of types) {
                const hit = components.find((c) => c.types.includes(type));
                if (hit) return hit.long_name;
            }
            return '';
        };
        return pick('locality', 'administrative_area_level_2', 'administrative_area_level_1') || '';
    }

    function applySuggestion(item) {
        if (!item) return;
        const address = document.getElementById('online-address');
        const city = document.getElementById('online-city');
        const search = document.getElementById('online-place-search');
        if (address && item.address) address.value = item.address;
        if (city && item.city) city.value = item.city;
        if (search && item.label) search.value = item.label;
        setCoords(item.lat, item.lng);
        if (marker && map) {
            const pos = { lat: item.lat, lng: item.lng };
            marker.setPosition(pos);
            map.panTo(pos);
            map.setZoom(16);
        }
        hideSuggestions();
    }

    function hideSuggestions() {
        const list = document.getElementById('online-place-suggestions');
        const search = document.getElementById('online-place-search');
        if (list) {
            list.hidden = true;
            list.innerHTML = '';
        }
        if (search) search.setAttribute('aria-expanded', 'false');
    }

    function renderSuggestions(items) {
        const list = document.getElementById('online-place-suggestions');
        const search = document.getElementById('online-place-search');
        if (!list) return;
        list.innerHTML = '';
        if (!items.length) {
            list.innerHTML = '<li class="place-empty">Sin coincidencias. Prueba otra búsqueda o marca el mapa.</li>';
            list.hidden = false;
            if (search) search.setAttribute('aria-expanded', 'true');
            return;
        }
        items.forEach((item) => {
            const li = document.createElement('li');
            li.setAttribute('role', 'option');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = item.label;
            const pick = (event) => {
                event.preventDefault();
                applySuggestion(item);
            };
            btn.addEventListener('mousedown', pick);
            btn.addEventListener('click', pick);
            li.appendChild(btn);
            list.appendChild(li);
        });
        list.hidden = false;
        if (search) search.setAttribute('aria-expanded', 'true');
    }

    async function lookupSuggestions(query) {
        const seq = ++searchSeq;
        try {
            const res = await fetch(placesSearchUrl + '?q=' + encodeURIComponent(query), {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();
            if (seq !== searchSeq) return;
            if (!res.ok) {
                renderSuggestions([]);
                showGeoError(data.error || 'No se pudieron cargar sugerencias.');
                return;
            }
            showGeoError('');
            renderSuggestions(data.suggestions || []);
        } catch (error) {
            if (seq !== searchSeq) return;
            renderSuggestions([]);
            showGeoError('Error de red al buscar ubicaciones.');
        }
    }

    function reverseViaFallback(lat, lng, seq) {
        return fetch(placesReverseUrl + '?lat=' + encodeURIComponent(lat) + '&lng=' + encodeURIComponent(lng), {
            headers: { Accept: 'application/json' },
        })
            .then((res) => res.json().then((data) => ({ ok: res.ok, data })))
            .then(({ ok, data }) => {
                if (seq !== reverseSeq || !ok || data.error) return;
                const address = document.getElementById('online-address');
                const city = document.getElementById('online-city');
                if (address && data.address) address.value = data.address;
                if (city && data.city) city.value = data.city;
            })
            .catch(() => {});
    }

    function reverseFill(lat, lng) {
        const seq = ++reverseSeq;
        if (geocoder) {
            return new Promise((resolve) => {
                geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                    if (seq !== reverseSeq) {
                        resolve();
                        return;
                    }
                    if (status === 'OK' && results?.[0]) {
                        const place = results[0];
                        const address = document.getElementById('online-address');
                        const city = document.getElementById('online-city');
                        if (address) address.value = place.formatted_address || address.value;
                        const fromPlace = cityFromComponents(place.address_components);
                        if (city && fromPlace) city.value = fromPlace;
                        resolve();
                        return;
                    }
                    reverseViaFallback(lat, lng, seq).finally(resolve);
                });
            });
        }
        return reverseViaFallback(lat, lng, seq);
    }

    function placeOnMap(lat, lng, zoom = 16) {
        setCoords(lat, lng);
        if (marker && map) {
            const pos = { lat, lng };
            marker.setPosition(pos);
            map.panTo(pos);
            map.setZoom(zoom);
        }
        return reverseFill(lat, lng);
    }

    function showGeoError(message) {
        const err = document.getElementById('online-geo-error');
        if (!err) return;
        err.hidden = !message;
        err.textContent = message || '';
    }

    function markGeoBanner(ok) {
        const banner = document.getElementById('online-geo-banner');
        if (!banner) return;
        banner.classList.toggle('is-ok', !!ok);
        banner.classList.toggle('is-busy', false);
        if (ok) {
            const title = banner.querySelector('strong');
            if (title) title.textContent = 'Ubicación del navegador aplicada';
        }
    }

    function setGeoBusy(busy, label) {
        const banner = document.getElementById('online-geo-banner');
        const allowBtn = document.getElementById('online-geo-allow');
        const retryBtn = document.getElementById('online-geo');
        if (banner) banner.classList.toggle('is-busy', !!busy);
        [allowBtn, retryBtn].forEach((btn) => {
            if (!btn) return;
            if (busy) {
                if (!btn.dataset.prevLabel) btn.dataset.prevLabel = btn.textContent;
                btn.disabled = true;
                btn.textContent = label || 'Obteniendo ubicación…';
            } else {
                btn.disabled = false;
                if (btn.dataset.prevLabel) {
                    btn.textContent = btn.dataset.prevLabel;
                    delete btn.dataset.prevLabel;
                }
            }
        });
    }

    function readGeoOnce(timeoutMs) {
        return new Promise((resolve) => {
            // getCurrentPosition must be invoked synchronously to keep the user-gesture chain.
            navigator.geolocation.getCurrentPosition(
                (position) => resolve({ ok: true, position }),
                (error) => resolve({ ok: false, error }),
                { enableHighAccuracy: true, timeout: timeoutMs, maximumAge: 0 }
            );
        });
    }

    async function requestBrowserLocation() {
        showGeoError('');
        markGeoBanner(false);

        if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            showGeoError('El navegador solo permite GPS en sitios seguros (https o localhost).');
            return false;
        }
        if (!navigator.geolocation) {
            showGeoError('Este navegador no soporta geolocalización. Busca la dirección a mano.');
            return false;
        }

        const maxAttempts = 3;
        setGeoBusy(true, 'Obteniendo ubicación…');

        let lastError = null;
        for (let attempt = 1; attempt <= maxAttempts; attempt++) {
            const timeoutMs = 12000 + ((attempt - 1) * 6000);
            if (attempt > 1) {
                setGeoBusy(true, 'Reintentando GPS (' + attempt + '/' + maxAttempts + ')…');
                showGeoError('Se agotó el tiempo del GPS. Reintentando automáticamente (' + attempt + ' de ' + maxAttempts + ')…');
            }

            const result = await readGeoOnce(timeoutMs);
            if (result.ok) {
                const { latitude, longitude } = result.position.coords;
                setCoords(latitude, longitude);
                try {
                    await ensureMap();
                } catch (error) {
                    // Coords already saved; map is optional visual feedback.
                }
                await placeOnMap(latitude, longitude);
                showGeoError('');
                markGeoBanner(true);
                setGeoBusy(false);
                return true;
            }

            lastError = result.error;
            // Auto-retry only on timeout; permission denial needs a fresh user click.
            if (!lastError || lastError.code !== 3 || attempt === maxAttempts) {
                break;
            }
        }

        markGeoBanner(false);
        setGeoBusy(false);
        const messages = {
            1: 'Permiso denegado. Activa la ubicación en el navegador o busca a mano.',
            2: 'No se pudo obtener la posición. Intenta de nuevo o busca a mano.',
            3: 'Se agotó el tiempo esperando el GPS tras varios intentos. Usa “Pedir ubicación otra vez” o busca a mano.',
        };
        const code = lastError?.code;
        showGeoError(messages[code] || ('Error de ubicación: ' + (lastError?.message || 'desconocido')));
        return false;
    }

    function wirePlaceSearch() {
        const search = document.getElementById('online-place-search');
        if (!search || search.dataset.wired) return;
        search.dataset.wired = '1';
        search.addEventListener('input', () => {
            const q = search.value.trim();
            clearTimeout(searchTimer);
            if (q.length < 3) {
                hideSuggestions();
                return;
            }
            searchTimer = setTimeout(() => lookupSuggestions(q), 280);
        });
        search.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') hideSuggestions();
        });
        search.addEventListener('blur', () => setTimeout(hideSuggestions, 180));
    }

    async function ensureMap() {
        const el = document.getElementById('online-map');
        wirePlaceSearch();
        if (!el || !window.__KADI_MAPS_KEY) return;
        if (window.google?.maps) {
            // already booted
        } else if (window.__KADI_MAPS_READY) {
            await Promise.race([
                window.__KADI_MAPS_READY,
                new Promise((resolve) => setTimeout(resolve, 5000)),
            ]);
        }
        if (!window.google?.maps) return;

        try {
            geocoder = geocoder || new google.maps.Geocoder();
        } catch (error) {
            geocoder = null;
        }

        if (!map) {
            map = new google.maps.Map(el, {
                center: defaultCenter,
                zoom: 13,
                disableDefaultUI: true,
                zoomControl: true,
                gestureHandling: 'greedy',
                styles: [
                    { elementType: 'geometry', stylers: [{ color: '#1d1d1f' }] },
                    { elementType: 'labels.text.stroke', stylers: [{ color: '#1d1d1f' }] },
                    { elementType: 'labels.text.fill', stylers: [{ color: '#b0b0b0' }] },
                    { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#2a2a2e' }] },
                    { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#0e0e12' }] },
                    { featureType: 'poi', stylers: [{ visibility: 'off' }] },
                ],
            });
            marker = new google.maps.Marker({
                map,
                position: defaultCenter,
                draggable: true,
                title: 'Tu ubicación',
            });
            marker.addListener('dragend', () => {
                const pos = marker.getPosition();
                placeOnMap(pos.lat(), pos.lng());
            });
            map.addListener('click', (event) => {
                placeOnMap(event.latLng.lat(), event.latLng.lng());
            });

            if (!document.getElementById('online-lat').value) {
                setCoords(defaultCenter.lat, defaultCenter.lng);
            }
        }

        setTimeout(() => {
            google.maps.event.trigger(map, 'resize');
            const lat = parseFloat(document.getElementById('online-lat').value) || defaultCenter.lat;
            const lng = parseFloat(document.getElementById('online-lng').value) || defaultCenter.lng;
            marker.setPosition({ lat, lng });
            map.setCenter({ lat, lng });
        }, 120);
    }

    function fillUploadForm() {
        const moto = motoBySlug(pendingSlug);
        if (!moto) return;
        const idInput = document.getElementById('online-moto-id');
        if (idInput) idInput.value = moto.id;
        const label = document.getElementById('online-moto-label');
        if (label) label.value = moto.name + ' · ' + moto.cc + ' cc';
        const color = document.getElementById('online-color');
        if (color) {
            color.replaceChildren(...(moto.colors || []).map((item) => {
                const option = document.createElement('option');
                option.value = item;
                option.textContent = item;
                return option;
            }));
        }
    }

    function setProgress(step) {
        currentStep = step;
        const progress = document.getElementById('online-progress');
        const fill = document.getElementById('online-progress-fill');
        if (progress) progress.hidden = false;
        if (fill) fill.style.width = ((step - 1) / 3 * 100) + '%';
        document.querySelectorAll('#online-step-indicator .step-pill').forEach((pill) => {
            const n = Number(pill.dataset.pill);
            pill.classList.toggle('is-on', n === step);
            pill.classList.toggle('is-done', n < step);
            pill.disabled = n >= step;
        });
    }

    function paintReview() {
        const moto = motoBySlug(pendingSlug);
        const branch = document.getElementById('online-branch');
        const branchText = branch?.selectedOptions?.[0]?.textContent || '';
        const files = [...document.querySelectorAll('#online-step-3 input[type=file]')];
        const fileLines = files.map((input) => {
            const label = input.previousElementSibling?.textContent || input.name;
            const name = input.files?.[0]?.name || '—';
            return `<li><strong>${label}</strong><span>${name}</span></li>`;
        }).join('');
        const box = document.getElementById('online-review');
        if (!box) return;
        box.innerHTML = `
            <h3>Revisa antes de enviar</h3>
            <ul class="review-list">
                <li><strong>Modelo</strong><span>${moto?.name || '—'}</span></li>
                <li><strong>Color</strong><span>${document.getElementById('online-color')?.value || '—'}</span></li>
                <li><strong>Sede</strong><span>${branchText}</span></li>
                <li><strong>Ciudad</strong><span>${document.getElementById('online-city')?.value || '—'}</span></li>
                <li><strong>Dirección</strong><span>${document.getElementById('online-address')?.value || '—'}</span></li>
                <li><strong>WhatsApp</strong><span>${document.getElementById('online-whatsapp')?.value || '—'}</span></li>
                <li><strong>GPS</strong><span>${document.getElementById('online-lat')?.value || '—'}, ${document.getElementById('online-lng')?.value || '—'}</span></li>
            </ul>
            <h3>Documentos</h3>
            <ul class="review-list">${fileLines}</ul>`;
    }

    function showPanel(name) {
        const auth = document.getElementById('online-step-auth');
        const blockedPanel = document.getElementById('online-step-blocked');
        const form = document.getElementById('online-upload-form');
        const done = document.getElementById('online-step-done');
        const progress = document.getElementById('online-progress');
        const kicker = document.getElementById('online-kicker');
        const title = document.getElementById('online-docs-title');
        const lead = document.getElementById('online-lead');

        if (auth) auth.hidden = name !== 'auth';
        if (blockedPanel) blockedPanel.hidden = name !== 'blocked';
        if (done) done.hidden = name !== 'done';
        if (form) form.hidden = !(typeof name === 'number');
        [1, 2, 3, 4].forEach((n) => {
            const el = document.getElementById('online-step-' + n);
            if (el) el.hidden = name !== n;
        });

        if (name === 'auth') {
            if (progress) progress.hidden = true;
            kicker.textContent = 'En línea · cuenta';
            title.textContent = 'Entra para subir';
            lead.textContent = 'Necesitas cuenta para el formulario.';
            return;
        }
        if (name === 'blocked') {
            if (progress) progress.hidden = true;
            if (form) form.hidden = true;
            kicker.textContent = 'En línea · bloqueado';
            title.textContent = 'Solicitud activa';
            lead.textContent = 'Tienes una solicitud activa, espera la aprobación.';
            return;
        }
        if (name === 'done') {
            if (progress) progress.hidden = true;
            if (form) form.hidden = true;
            kicker.textContent = 'En línea · listo';
            title.textContent = 'Solicitud pendiente';
            lead.textContent = 'Gracias. Te contactaremos por WhatsApp.';
            return;
        }

        const copy = {
            1: ['En línea · paso 1 de 4', 'Datos de la solicitud', 'Modelo, sede, ciudad, dirección y WhatsApp.'],
            2: ['En línea · paso 2 de 4', 'Ubicación exacta', 'Busca en Maps o permite el GPS del navegador.'],
            3: ['En línea · paso 3 de 4', 'Documentos', 'Cédula frente, RIF, licencia 2DA y constancia.'],
            4: ['En línea · paso 4 de 4', 'Revisión', 'Confirma todo antes de enviar.'],
        };
        setProgress(name);
        fillUploadForm();
        kicker.textContent = copy[name][0];
        title.textContent = copy[name][1];
        lead.textContent = copy[name][2];
        hideSuggestions();
        if (name === 2) ensureMap();
        if (name === 4) paintReview();
        online.querySelector('.docs-shell')?.scrollTo?.({ top: 0 });
        online.scrollTop = 0;
    }

    function validateStep(step) {
        const err = document.getElementById('online-err-' + step);
        let ok = true;
        if (step === 1) {
            ['online-color', 'online-branch', 'online-city', 'online-address', 'online-whatsapp', 'online-moto-id'].forEach((id) => {
                const el = document.getElementById(id);
                if (!el) return;
                if (typeof el.checkValidity === 'function' && !el.checkValidity()) {
                    ok = false;
                    el.reportValidity?.();
                }
                if (!String(el.value || '').trim()) ok = false;
            });
            const phone = document.getElementById('online-whatsapp')?.value?.trim() || '';
            if (phone.replace(/\D/g, '').length < 10) ok = false;
        } else if (step === 2) {
            const lat = parseFloat(document.getElementById('online-lat')?.value || '');
            const lng = parseFloat(document.getElementById('online-lng')?.value || '');
            ok = Number.isFinite(lat) && Number.isFinite(lng);
        } else if (step === 3) {
            const files = [...document.querySelectorAll('#online-step-3 input[type=file]')];
            ok = files.every((input) => input.files && input.files.length > 0);
        }
        if (err) err.hidden = ok;
        return ok;
    }

    function openPersona(slug) {
        pendingSlug = slug || '';
        if (typeof persona.showModal === 'function' && !persona.open) persona.showModal();
    }

    function openOnline(slug, startAt) {
        pendingSlug = slug || '';
        syncAuthLinks();
        showGeoError('');
        if (startAt === 'done') {
            showPanel('done');
        } else if (!authed) {
            showPanel('auth');
        } else if (blocked) {
            showPanel('blocked');
        } else {
            showPanel(1);
        }
        if (typeof online.showModal === 'function' && !online.open) online.showModal();
    }

    window.openPersonaDocs = openPersona;
    window.openOnlineDocs = openOnline;
    window.openDocsModal = (slug) => openOnline(slug);

    document.addEventListener('click', (event) => {
        const personaBtn = event.target.closest('[data-open-persona]');
        if (personaBtn) {
            event.preventDefault();
            event.stopPropagation();
            openPersona(personaBtn.dataset.openPersona || '');
            return;
        }
        const onlineBtn = event.target.closest('[data-open-online]');
        if (onlineBtn) {
            event.preventDefault();
            event.stopPropagation();
            openOnline(onlineBtn.dataset.openOnline || '');
        }
    });

    persona.querySelectorAll('[data-close-persona]').forEach((btn) => btn.addEventListener('click', () => persona.close()));
    online.querySelectorAll('[data-close-online]').forEach((btn) => btn.addEventListener('click', () => online.close()));

    online.addEventListener('click', (event) => {
        const geoAllow = event.target.closest('#online-geo-allow, #online-geo');
        if (geoAllow) {
            event.preventDefault();
            event.stopPropagation();
            if (geoAllow.disabled) return;
            requestBrowserLocation();
            return;
        }
        const geoDeny = event.target.closest('#online-geo-deny');
        if (geoDeny) {
            event.preventDefault();
            event.stopPropagation();
            showGeoError('');
            markGeoBanner(false);
            setGeoBusy(false);
            document.getElementById('online-place-search')?.focus();
            return;
        }
        const next = event.target.closest('[data-next-step]');
        if (next) {
            const to = Number(next.dataset.nextStep);
            if (!validateStep(currentStep)) return;
            showPanel(to);
            return;
        }
        const prev = event.target.closest('[data-prev-step]');
        if (prev) {
            showPanel(Number(prev.dataset.prevStep));
            return;
        }
        const pill = event.target.closest('.step-pill[data-pill]');
        if (pill && !pill.disabled) {
            const to = Number(pill.dataset.pill);
            if (to < currentStep) showPanel(to);
        }
    });

    const bootSlug = @json(request('solicitar') ?: '');
    const bootCanal = @json(request('canal'));
    const bootPaso = @json(request('paso'));
    const justSubmitted = {{ session('online_submitted') ? 'true' : 'false' }};
    const appUrl = @json(session('online_application_url'));
    if (justSubmitted || bootPaso === 'done') {
        pendingSlug = bootSlug || '';
        const link = document.getElementById('online-done-link');
        if (link && appUrl) link.href = appUrl;
        openOnline(pendingSlug, 'done');
    } else if (bootSlug && bootCanal === 'en_linea' && bootPaso === 'upload') {
        openOnline(bootSlug);
    }
})();
</script>
