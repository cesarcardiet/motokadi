@php
    use App\Models\AppNotification;

    $user = auth()->user();
    $roleSlug = $user?->role?->slug;

    $bellHref = match ($roleSlug) {
        'administrador' => route('admin.payments.index'),
        'vendedor' => route('staff.applications.index'),
        default => route('client.applications.index'),
    };

    $unread = AppNotification::query()
        ->where('user_id', $user->id)
        ->whereNull('read_at')
        ->count();

    $latestId = (int) (AppNotification::query()
        ->where('user_id', $user->id)
        ->max('id') ?? 0);

    $bellItems = AppNotification::query()
        ->where('user_id', $user->id)
        ->latest()
        ->limit(8)
        ->get()
        ->map(fn (AppNotification $notification) => [
            'id' => $notification->id,
            'title' => $notification->title,
            'text' => $notification->body,
            'href' => $notification->link ?: $bellHref,
            'unread' => $notification->read_at === null,
            'when' => $notification->created_at?->diffForHumans(),
        ])
        ->all();
@endphp

<div
    class="notif-bell topbar-notif"
    data-notif
    data-notif-feed="{{ route('notifications.feed') }}"
    data-notif-read="{{ route('notifications.read') }}"
    data-notif-csrf="{{ csrf_token() }}"
    data-notif-latest="{{ $latestId }}"
    data-notif-unread="{{ $unread }}"
>
    <button class="notif-bell-btn" type="button" aria-label="Notificaciones" aria-expanded="false" data-notif-toggle>
        <span class="notif-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path>
                <path d="M9 17a3 3 0 0 0 6 0"></path>
            </svg>
        </span>
        @if ($unread > 0)
            <span class="notif-dot" data-notif-dot>{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </button>
    <div class="notif-panel" hidden data-notif-panel>
        <div class="notif-head">
            <p class="notif-title">Notificaciones</p>
            <button class="notif-mute" type="button" data-notif-mute title="Silenciar sonido">Sonido</button>
        </div>
        <div class="notif-list" data-notif-list>
            @forelse ($bellItems as $item)
                <a href="{{ $item['href'] }}" @class(['is-unread' => $item['unread']]) data-notif-id="{{ $item['id'] }}">
                    <strong>{{ $item['title'] }}</strong>
                    <span>{{ $item['text'] }}</span>
                    <small>{{ $item['when'] }}</small>
                </a>
            @empty
                <p class="notif-empty">Sin novedades por ahora.</p>
            @endforelse
        </div>
        <a class="notif-all" href="{{ $bellHref }}">Ver todo</a>
    </div>
</div>
<div class="notif-toast" hidden data-notif-toast aria-live="polite"></div>
