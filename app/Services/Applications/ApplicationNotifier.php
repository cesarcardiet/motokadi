<?php

namespace App\Services\Applications;

use App\Models\AppNotification;
use App\Models\CreditApplication;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApplicationNotifier
{
    public function created(CreditApplication $application): void
    {
        $this->dispatch('application.created', $application, [
            'client_title' => 'Solicitud recibida',
            'client_message' => 'Recibimos tu solicitud de financiamiento. Queda pendiente de revisión.',
            'admin_title' => 'Nueva solicitud',
            'admin_message' => 'Nueva solicitud activa pendiente de revisión.',
            'client_link' => route('client.applications.show', $application),
            'admin_link' => route('staff.applications.show', $application),
        ]);
    }

    public function approved(CreditApplication $application): void
    {
        $this->dispatch('application.approved', $application, [
            'client_title' => 'Solicitud aprobada',
            'client_message' => 'Tu solicitud fue aprobada. Ya puedes ver el contrato y tus cuotas.',
            'admin_title' => 'Aprobación registrada',
            'admin_message' => 'Solicitud #'.$application->id.' aprobada definitivamente.',
            'client_link' => route('client.payments.index'),
            'admin_link' => route('staff.applications.show', $application),
        ]);
    }

    public function rejected(CreditApplication $application, string $reason): void
    {
        $this->dispatch('application.rejected', $application, [
            'client_title' => 'Solicitud rechazada',
            'client_message' => 'Tu solicitud fue rechazada. Motivo: '.$reason,
            'admin_title' => 'Rechazo registrado',
            'admin_message' => 'Solicitud #'.$application->id.' rechazada. Motivo: '.$reason,
            'client_link' => route('client.applications.show', $application),
            'admin_link' => route('staff.applications.show', $application),
            'reason' => $reason,
        ]);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function dispatch(string $event, CreditApplication $application, array $extra = []): void
    {
        $application->loadMissing(['client', 'motorcycle', 'branch']);

        $client = $application->client;
        if ($client) {
            AppNotification::query()->create([
                'user_id' => $client->id,
                'type' => $event,
                'title' => (string) ($extra['client_title'] ?? 'Actualización'),
                'body' => (string) ($extra['client_message'] ?? ''),
                'link' => $extra['client_link'] ?? null,
            ]);
        }

        $admins = User::query()
            ->whereHas('role.permissions', fn ($query) => $query->where('slug', 'solicitudes.revisar'))
            ->get(['id', 'name', 'email', 'phone']);

        foreach ($admins as $admin) {
            AppNotification::query()->create([
                'user_id' => $admin->id,
                'type' => $event,
                'title' => (string) ($extra['admin_title'] ?? 'Actualización'),
                'body' => (string) ($extra['admin_message'] ?? ''),
                'link' => $extra['admin_link'] ?? null,
            ]);
        }

        $payload = [
            'event' => $event,
            'sent_at' => now()->toIso8601String(),
            'application' => [
                'id' => $application->id,
                'status' => $application->status->value,
                'status_label' => $application->status->label(),
                'channel' => $application->channel->value,
                'color' => $application->color,
                'city' => $application->city,
                'whatsapp_phone' => $application->whatsapp_phone,
                'motorcycle' => $application->motorcycle?->name,
                'branch' => $application->branch?->name,
                'monthly_payment' => $application->monthly_payment,
            ],
            'recipients' => [
                'client' => [
                    'name' => $client?->name,
                    'email' => $client?->email,
                    'phone' => $application->whatsapp_phone ?: $client?->phone,
                    'message' => $extra['client_message'] ?? null,
                ],
                'admins' => $admins
                    ->map(fn (User $user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'message' => $extra['admin_message'] ?? null,
                    ])
                    ->values()
                    ->all(),
            ],
            'extra' => collect($extra)->except([
                'client_message', 'admin_message', 'client_title', 'admin_title', 'client_link', 'admin_link',
            ])->all(),
        ];

        $url = config('services.notifications.webhook_url');
        $secret = config('services.notifications.webhook_secret');

        if (! filled($url)) {
            Log::channel(config('services.notifications.log_channel', 'stack'))
                ->info('application.notification', $payload);

            return;
        }

        try {
            $request = Http::timeout(8)->acceptJson()->asJson();

            if (filled($secret)) {
                $request = $request->withHeaders([
                    'X-Kadi-Signature' => hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE) ?: '', (string) $secret),
                ]);
            }

            $response = $request->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('application.notification.webhook_failed', [
                    'event' => $event,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (Throwable $exception) {
            Log::warning('application.notification.webhook_exception', [
                'event' => $event,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
