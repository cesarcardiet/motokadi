<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Financiamiento') · Kadi Motors</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,700;1,700&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/kadi.css') }}?v=52">
</head>
<body @class(['has-sidebar' => auth()->check()])>
    <div id="save-overlay" class="save-overlay" hidden aria-live="polite" aria-busy="true">
        <div class="save-card">
            <div class="save-spinner" aria-hidden="true"></div>
            <p class="save-title">Guardando…</p>
            <p class="save-sub">Un momento, no cierres la página.</p>
        </div>
    </div>
    @auth
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="app-sidebar" aria-expanded="false">Menú</button>
        @include('partials.sidebar')
        <div class="app-frame">
            <header class="app-topbar">
                <div class="app-topbar-inner">
                    <div class="app-topbar-copy">
                        <p class="app-topbar-kicker">Kadi Motors</p>
                        <p class="app-topbar-title">@yield('title', 'Panel')</p>
                    </div>
                    @include('partials.notifications-bell')
                </div>
            </header>
            <main class="wrap app-main">
                @if (session('status')) <div class="flash">{{ session('status') }}</div> @endif
                @if (session('error')) <div class="flash error">{{ session('error') }}</div> @endif
                @yield('content')
            </main>
            <footer>
                <div class="wrap footer-inner">
                    <div>
                        <strong>Kadi Motors</strong>
                        <p>Cuotas a 9 meses. Classic, Diamante y Tundra usan la tabla de ejemplo. El resto es precio de referencia con la misma fórmula.</p>
                    </div>
                    <div>
                        <a href="{{ route('requirements') }}">Requisitos</a><br>
                        <a href="{{ route('simulation') }}">Simulación</a>
                    </div>
                </div>
            </footer>
        </div>
    @else
        <header class="topbar">
            <div class="wrap topbar-inner">
                <a class="brand" href="{{ route('home') }}">
                    <img class="logo-mark" src="{{ asset('images/logo-kadi.png') }}" alt="Kadi">
                </a>
                <nav class="nav">
                    <a href="{{ route('catalog.index') }}">Catálogo</a>
                    <a href="{{ route('home') }}#calculadora">Calculadora</a>
                    <a href="{{ route('simulation') }}">Cuotas</a>
                    <a href="{{ route('requirements') }}">Requisitos</a>
                    <a href="{{ route('login') }}">Ingresar</a>
                    <a class="btn small" href="{{ route('register') }}">Crear cuenta</a>
                </nav>
            </div>
        </header>
        <main class="wrap">
            @if (session('status')) <div class="flash">{{ session('status') }}</div> @endif
            @if (session('error')) <div class="flash error">{{ session('error') }}</div> @endif
            @yield('content')
        </main>
        <footer>
            <div class="wrap footer-inner">
                <div>
                    <strong>Kadi Motors</strong>
                    <p>Cuotas a 9 meses. Classic, Diamante y Tundra usan la tabla de ejemplo. El resto es precio de referencia con la misma fórmula.</p>
                </div>
                <div>
                    <a href="{{ route('requirements') }}">Requisitos</a><br>
                    <a href="{{ route('simulation') }}">Simulación</a>
                </div>
            </div>
        </footer>
    @endauth

    @if (request()->routeIs('home', 'catalog.index', 'catalog.show', 'requirements'))
        @include('partials.apply-modal')
        @include('partials.docs-modal')
    @endif

    <script>
        (() => {
            const overlay = document.getElementById('save-overlay');
            const VIEW_KEY = 'kadi:keep-view';

            const showSaving = () => {
                if (!overlay) return;
                overlay.hidden = false;
                document.body.classList.add('is-saving');
            };

            const hideSaving = () => {
                if (!overlay) return;
                overlay.hidden = true;
                document.body.classList.remove('is-saving');
            };

            const restoreView = () => {
                let payload = null;
                try {
                    payload = JSON.parse(sessionStorage.getItem(VIEW_KEY) || 'null');
                } catch (error) {
                    payload = null;
                }
                if (!payload || payload.path !== location.pathname) return;
                sessionStorage.removeItem(VIEW_KEY);

                const apply = () => {
                    if (payload.anchor) {
                        const el = document.getElementById(payload.anchor);
                        if (el) {
                            el.scrollIntoView({ block: 'center', behavior: 'instant' in window ? 'instant' : 'auto' });
                            el.classList.add('just-saved');
                            setTimeout(() => el.classList.remove('just-saved'), 1200);
                            return;
                        }
                    }
                    if (typeof payload.y === 'number') {
                        window.scrollTo({ top: payload.y, behavior: 'auto' });
                    }
                };

                requestAnimationFrame(() => requestAnimationFrame(apply));
            };

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;
                if ((form.getAttribute('method') || 'get').toLowerCase() === 'get') return;
                if (form.dataset.noOverlay === '1') return;

                sessionStorage.setItem(VIEW_KEY, JSON.stringify({
                    path: location.pathname,
                    y: window.scrollY || window.pageYOffset || 0,
                    anchor: form.dataset.keepView || null,
                }));

                showSaving();
                form.querySelectorAll('button[type=submit], input[type=submit]').forEach((btn) => {
                    btn.disabled = true;
                    if (btn.tagName === 'BUTTON' && !btn.dataset.originalLabel) {
                        btn.dataset.originalLabel = btn.textContent;
                        btn.textContent = 'Guardando…';
                    }
                });
            }, true);

            window.addEventListener('pageshow', () => {
                hideSaving();
                restoreView();
            });
            document.addEventListener('DOMContentLoaded', restoreView);
        })();
    </script>

    @auth
        <script>
            (() => {
                const toggle = document.querySelector('[data-sidebar-toggle]');
                const body = document.body;
                if (toggle) {
                    toggle.addEventListener('click', () => {
                        const open = body.classList.toggle('sidebar-open');
                        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                    });
                }

                const closeSidebar = () => {
                    body.classList.remove('sidebar-open');
                    if (toggle) toggle.setAttribute('aria-expanded', 'false');
                };

                const scrollToHash = () => {
                    if (!location.hash || location.hash.length < 2) return;
                    const el = document.getElementById(location.hash.slice(1));
                    if (!el) return;
                    requestAnimationFrame(() => {
                        el.scrollIntoView({ block: 'start', behavior: 'smooth' });
                    });
                };

                document.querySelectorAll('.sidebar-nav a[href*="#"]').forEach((link) => {
                    link.addEventListener('click', () => {
                        closeSidebar();
                        const url = new URL(link.href, location.origin);
                        if (url.pathname === location.pathname && url.hash) {
                            // Same page: force scroll even if hash already matches.
                            setTimeout(() => {
                                const el = document.getElementById(url.hash.slice(1));
                                if (el) el.scrollIntoView({ block: 'start', behavior: 'smooth' });
                            }, 40);
                        }
                    });
                });
                window.addEventListener('hashchange', scrollToHash);
                document.addEventListener('DOMContentLoaded', scrollToHash);
                window.addEventListener('pageshow', scrollToHash);

                const root = document.querySelector('[data-notif]');
                const notifToggle = document.querySelector('[data-notif-toggle]');
                const notifPanel = document.querySelector('[data-notif-panel]');
                const list = document.querySelector('[data-notif-list]');
                const muteBtn = document.querySelector('[data-notif-mute]');
                const toast = document.querySelector('[data-notif-toast]');
                if (!root || !notifToggle || !notifPanel || !list) return;

                const MUTE_KEY = 'kadi:notif-mute';
                const feedUrl = root.dataset.notifFeed;
                const readUrl = root.dataset.notifRead;
                const csrf = root.dataset.notifCsrf;
                let latestId = Number(root.dataset.notifLatest || 0);
                let muted = localStorage.getItem(MUTE_KEY) === '1';
                let audioCtx = null;
                let toastTimer = null;

                const syncMuteUi = () => {
                    if (!muteBtn) return;
                    muteBtn.classList.toggle('is-muted', muted);
                    muteBtn.textContent = muted ? 'Silenciado' : 'Sonido';
                    muteBtn.title = muted ? 'Activar sonido' : 'Silenciar sonido';
                };
                syncMuteUi();

                const ensureAudio = () => {
                    try {
                        const Ctx = window.AudioContext || window.webkitAudioContext;
                        if (!Ctx) return null;
                        audioCtx = audioCtx || new Ctx();
                        if (audioCtx.state === 'suspended') audioCtx.resume();
                        return audioCtx;
                    } catch (error) {
                        return null;
                    }
                };

                const unlockAudio = () => {
                    ensureAudio();
                    document.removeEventListener('pointerdown', unlockAudio);
                    document.removeEventListener('keydown', unlockAudio);
                };
                document.addEventListener('pointerdown', unlockAudio, { once: true });
                document.addEventListener('keydown', unlockAudio, { once: true });

                const playChime = () => {
                    if (muted) return;
                    const ctx = ensureAudio();
                    if (!ctx) return;
                    try {
                        const now = ctx.currentTime;
                        [880, 1174].forEach((freq, i) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.type = 'sine';
                            osc.frequency.value = freq;
                            gain.gain.setValueAtTime(0.0001, now);
                            gain.gain.exponentialRampToValueAtTime(0.08, now + 0.02 + i * 0.08);
                            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.28 + i * 0.1);
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start(now + i * 0.08);
                            osc.stop(now + 0.35 + i * 0.1);
                        });
                    } catch (error) {}
                };

                const escapeHtml = (value) => String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');

                const setBadge = (count) => {
                    let dot = notifToggle.querySelector('[data-notif-dot]');
                    if (count <= 0) {
                        if (dot) dot.remove();
                        return;
                    }
                    if (!dot) {
                        dot = document.createElement('span');
                        dot.className = 'notif-dot';
                        dot.setAttribute('data-notif-dot', '');
                        notifToggle.appendChild(dot);
                    }
                    dot.textContent = count > 9 ? '9+' : String(count);
                };

                const renderItems = (items) => {
                    if (!items.length) {
                        list.innerHTML = '<p class="notif-empty">Sin novedades por ahora.</p>';
                        return;
                    }
                    list.innerHTML = items.map((item) => `
                        <a href="${escapeHtml(item.href)}" class="${item.unread ? 'is-unread' : ''}" data-notif-id="${item.id}">
                            <strong>${escapeHtml(item.title)}</strong>
                            <span>${escapeHtml(item.text)}</span>
                            <small>${escapeHtml(item.when || '')}</small>
                        </a>
                    `).join('');
                };

                const showToast = (item) => {
                    if (!toast || !item) return;
                    toast.innerHTML = `<strong>${escapeHtml(item.title)}</strong><span>${escapeHtml(item.text)}</span>`;
                    toast.hidden = false;
                    clearTimeout(toastTimer);
                    toastTimer = setTimeout(() => { toast.hidden = true; }, 5200);
                };

                const closeNotif = () => {
                    notifPanel.hidden = true;
                    notifToggle.setAttribute('aria-expanded', 'false');
                };

                const openNotif = () => {
                    notifPanel.hidden = false;
                    notifToggle.setAttribute('aria-expanded', 'true');
                    root.classList.remove('has-new');
                    fetch(readUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                    }).catch(() => {});
                    setBadge(0);
                    list.querySelectorAll('a.is-unread').forEach((el) => el.classList.remove('is-unread'));
                };

                notifToggle.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    if (notifPanel.hidden) openNotif();
                    else closeNotif();
                });
                notifPanel.addEventListener('click', (event) => event.stopPropagation());
                document.addEventListener('click', closeNotif);

                if (muteBtn) {
                    muteBtn.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        muted = !muted;
                        localStorage.setItem(MUTE_KEY, muted ? '1' : '0');
                        syncMuteUi();
                        if (!muted) playChime();
                    });
                }

                const livePayments = document.querySelector('[data-live-payments]');
                const onPaymentsPage = Boolean(livePayments);
                let reloading = false;
                let paymentsStamp = null;

                const refreshPaymentsView = () => {
                    if (!onPaymentsPage || reloading) return;
                    reloading = true;
                    setTimeout(() => location.reload(), 600);
                };

                const poll = () => {
                    fetch(feedUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                        .then((res) => (res.ok ? res.json() : null))
                        .then((data) => {
                            if (!data || reloading) return;
                            const prevLatest = latestId;
                            const arrived = Number(data.latest_id || 0) > prevLatest;
                            if (arrived) {
                                const fresh = (data.items || []).find((item) => Number(item.id) > prevLatest);
                                playChime();
                                showToast(fresh || data.items?.[0]);
                                root.classList.add('has-new');
                            }
                            latestId = Math.max(latestId, Number(data.latest_id || 0));
                            setBadge(Number(data.unread || 0));
                            if (notifPanel.hidden) renderItems(data.items || []);
                            else if (arrived) renderItems(data.items || []);

                            if (onPaymentsPage && data.payments_stamp) {
                                if (paymentsStamp === null) {
                                    paymentsStamp = data.payments_stamp;
                                } else if (paymentsStamp !== data.payments_stamp) {
                                    paymentsStamp = data.payments_stamp;
                                    refreshPaymentsView();
                                }
                            }
                        })
                        .catch(() => {});
                };

                const pollMs = onPaymentsPage ? 3000 : 10000;
                setInterval(poll, pollMs);
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) poll();
                });
                if (onPaymentsPage) poll();
            })();
        </script>
    @endauth
</body>
</html>
