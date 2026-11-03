@php
    use App\Enums\VoucherStatus;
    use App\Models\PaymentVoucher;

    $user = auth()->user();
    $roleSlug = $user?->role?->slug;
    $current = request()->route()?->getName() ?? '';
    $pendingPayments = $roleSlug === 'administrador'
        ? PaymentVoucher::query()->where('status', VoucherStatus::Pending)->count()
        : 0;

    $isActive = function (string|array $names) use ($current): bool {
        foreach ((array) $names as $name) {
            if ($name === '') {
                continue;
            }
            if (str_ends_with($name, '.*')) {
                $prefix = substr($name, 0, -2);
                if ($current === $prefix || str_starts_with($current, $prefix.'.')) {
                    return true;
                }
                continue;
            }
            if ($current === $name) {
                return true;
            }
        }

        return false;
    };

    $adminItems = [
        ['label' => 'Administración', 'route' => 'admin.dashboard', 'match' => ['admin.dashboard']],
        ['label' => 'Bandeja', 'route' => 'staff.applications.index', 'match' => ['staff.applications.*'], 'params' => ['estado' => 'activas']],
        ['label' => $pendingPayments > 0 ? 'Pagos ('.$pendingPayments.')' : 'Pagos', 'route' => 'admin.payments.index', 'match' => ['admin.payments.*'], 'params' => ['estado' => 'pendiente']],
        ['label' => 'Tasa USD/Bs', 'route' => 'admin.exchange-rate.edit', 'match' => ['admin.exchange-rate.*']],
        ['label' => 'Cartera', 'route' => 'admin.contracts.index', 'match' => ['admin.contracts.*']],
        ['label' => 'Perfiles crédito', 'route' => 'admin.profiles.index', 'match' => ['admin.profiles.*']],
        ['label' => 'Cuotas', 'route' => 'simulation', 'match' => ['simulation']],
        ['label' => 'Sedes', 'route' => 'admin.branches.index', 'match' => ['admin.branches.*']],
        ['label' => 'Usuarios', 'route' => 'admin.users.index', 'match' => ['admin.users.*']],
        ['label' => 'Motos', 'route' => 'admin.motorcycles.index', 'match' => ['admin.motorcycles.*']],
        ['label' => 'Roles', 'route' => 'admin.roles.index', 'match' => ['admin.roles.*']],
        ['label' => 'Catálogo', 'route' => 'catalog.index', 'match' => ['catalog.index', 'catalog.show']],
    ];

    $staffItems = [
        ['label' => 'Bandeja', 'route' => 'staff.applications.index', 'match' => ['staff.applications.*'], 'params' => ['estado' => 'activas']],
        ['label' => 'Cuotas', 'route' => 'simulation', 'match' => ['simulation']],
        ['label' => 'Catálogo', 'route' => 'catalog.index', 'match' => ['catalog.index', 'catalog.show']],
        ['label' => 'Requisitos', 'route' => 'requirements', 'match' => ['requirements']],
    ];

    $clientItems = [
        ['label' => 'Mis solicitudes', 'route' => 'client.applications.index', 'match' => ['client.applications.*']],
        ['label' => 'Mis pagos', 'route' => 'client.payments.index', 'match' => ['client.payments.index', 'client.payments.store']],
        ['label' => 'Comprobantes', 'route' => 'client.payments.vouchers', 'match' => ['client.payments.vouchers', 'client.payments.voucher.pdf']],
        ['label' => 'Catálogo', 'route' => 'catalog.index', 'match' => ['catalog.index', 'catalog.show']],
        ['label' => 'Cuotas', 'route' => 'simulation', 'match' => ['simulation']],
        ['label' => 'Calculadora', 'route' => 'home', 'hash' => '#calculadora', 'match' => ['home']],
        ['label' => 'Requisitos', 'route' => 'requirements', 'match' => ['requirements']],
    ];

    $items = match ($roleSlug) {
        'administrador' => $adminItems,
        'vendedor' => $staffItems,
        default => $clientItems,
    };

    $roleLabel = match ($roleSlug) {
        'administrador' => 'Administrador',
        'vendedor' => 'Vendedor',
        'cliente' => 'Usuario',
        default => 'Cuenta',
    };
@endphp

<aside class="app-sidebar" id="app-sidebar" aria-label="Menú principal">
    <div class="sidebar-brand">
        <a href="{{ route('home') }}">
            <img class="logo-mark" src="{{ asset('images/logo-kadi.png') }}" alt="Kadi">
        </a>
        <p class="sidebar-role">{{ $roleLabel }}</p>
    </div>

    <nav class="sidebar-nav">
        @foreach ($items as $item)
            @php
                $href = route($item['route'], $item['params'] ?? []);
                if (! empty($item['hash'])) {
                    $href .= $item['hash'];
                }
                $active = $isActive($item['match'] ?? [$item['route']]);
            @endphp
            <a href="{{ $href }}" @class(['is-on' => $active])>{{ $item['label'] }}</a>
        @endforeach
    </nav>

    <div class="sidebar-foot">
        <p class="sidebar-user">{{ $user->name }}</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn small ghost sidebar-logout" type="submit">Salir</button>
        </form>
    </div>
</aside>
