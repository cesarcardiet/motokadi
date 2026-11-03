@php
    $company = config('company');
    $logoPath = $company['logo'] ?? null;
    $logoSrc = null;
    if ($logoPath && is_file($logoPath)) {
        $bytes = file_get_contents($logoPath);
        $mime = str_starts_with($bytes, "\xFF\xD8\xFF") ? 'image/jpeg' : 'image/png';
        $logoSrc = 'data:'.$mime.';base64,'.base64_encode($bytes);
    }

    $docNumber = sprintf('RC-%s-%02d-%04d', $contract->contract_number, $installment->number, $voucher->id);
    $amountUsd = (float) $voucher->amount;
    $amountVes = $voucher->amountInVes();
    $status = strtoupper($voucher->status->label());
    $branch = $contract->branch;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $docNumber }}</title>
    <style>
        @page { margin: 12mm 12mm 12mm 12mm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1a1a1a;
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        table { border-collapse: collapse; }
        td, th { vertical-align: top; }
        .muted { color: #666666; }
        .red { color: #b10f0f; }
        .w100 { width: 100%; }

        /* Franja superior: bloque sólido, sin border-top mezclado */
        .stripe {
            background-color: #b10f0f;
            height: 8px;
            width: 100%;
            font-size: 1px;
            line-height: 1px;
        }

        .pad { padding: 10px 12px; }
        .pad-sm { padding: 8px 12px; }

        .rule {
            border-bottom: 1px solid #d8d8d8;
        }

        .brand-name {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 2px 0;
        }
        .brand-line { margin: 0; font-size: 9px; color: #444444; }

        .title-cell {
            text-align: right;
            width: 42%;
        }
        .title-label {
            margin: 0;
            font-size: 12px;
            font-weight: bold;
            color: #b10f0f;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .title-number {
            margin: 4px 0 0 0;
            font-size: 11px;
            font-weight: bold;
        }
        .title-meta { margin: 2px 0 0 0; font-size: 9px; color: #555555; }

        .logo {
            width: 52px;
            height: 52px;
        }

        .section {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #b10f0f;
            margin: 0 0 5px 0;
        }
        .party-name {
            font-size: 11px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }
        .party-line { margin: 0 0 2px 0; font-size: 9.5px; }

        .split td {
            width: 50%;
            padding: 10px 12px;
        }
        .split .left { border-right: 1px solid #d8d8d8; }

        .status-row {
            background-color: #f4f4f4;
            padding: 7px 12px;
            font-size: 9.5px;
        }
        .status-text {
            font-weight: bold;
            text-transform: uppercase;
        }

        .block-title {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #b10f0f;
            margin: 0 0 6px 0;
            padding-bottom: 3px;
            border-bottom: 2px solid #b10f0f;
        }

        .items th {
            background-color: #1a1a1a;
            color: #ffffff;
            font-size: 8.5px;
            text-transform: uppercase;
            padding: 6px 7px;
            text-align: left;
        }
        .items td {
            padding: 7px;
            border-bottom: 1px solid #e6e6e6;
            font-size: 9.5px;
        }
        .items .num { text-align: right; }
        .items .ctr { text-align: center; }

        .unit {
            margin-top: 8px;
            padding: 7px 8px;
            background-color: #f7f7f7;
            font-size: 9px;
        }

        .totals th {
            text-align: left;
            font-weight: normal;
            color: #555555;
            padding: 5px 8px;
            background-color: #fafafa;
            border-bottom: 1px solid #ececec;
            font-size: 9.5px;
            width: 58%;
        }
        .totals td {
            text-align: right;
            font-weight: bold;
            padding: 5px 8px;
            border-bottom: 1px solid #ececec;
            font-size: 9.5px;
        }
        .totals tr.grand th,
        .totals tr.grand td {
            background-color: #b10f0f;
            color: #ffffff;
            border-bottom: 0;
            font-size: 10.5px;
            padding: 7px 8px;
        }

        .footer {
            font-size: 8px;
            color: #666666;
            padding: 8px 12px 10px 12px;
        }
        .stamp {
            text-align: center;
            color: #b10f0f;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            padding: 6px 4px;
        }
    </style>
</head>
<body>
    {{-- Franja roja limpia (sin borde exterior que DomPDF descuadre) --}}
    <div class="stripe">&nbsp;</div>

    <table class="w100 rule">
        <tr>
            <td class="pad" style="width:58%;">
                <table>
                    <tr>
                        <td style="width:58px; padding-right:8px;">
                            @if ($logoSrc)
                                <img class="logo" src="{{ $logoSrc }}" alt="Kadi">
                            @endif
                        </td>
                        <td>
                            <p class="brand-name">{{ $company['trade_name'] }}</p>
                            <p class="brand-line"><strong>{{ $company['legal_name'] }}</strong></p>
                            <p class="brand-line">RIF: {{ $company['rif'] }}</p>
                            <p class="brand-line">{{ $company['address'] }}</p>
                            <p class="brand-line">Tel. {{ $company['phone'] }} · {{ $company['email'] }}</p>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="pad title-cell">
                <p class="title-label">Comprobante de pago</p>
                <p class="title-number">Nº {{ $docNumber }}</p>
                <p class="title-meta">Fecha emisión: {{ now()->format('d/m/Y H:i') }}</p>
                <p class="title-meta">Fecha pago: {{ $voucher->paid_on?->format('d/m/Y') ?? '—' }}</p>
            </td>
        </tr>
    </table>

    <table class="w100 split rule">
        <tr>
            <td class="left">
                <p class="section">Emisor</p>
                <p class="party-name">{{ $company['legal_name'] }}</p>
                <p class="party-line"><span class="muted">RIF:</span> <strong>{{ $company['rif'] }}</strong></p>
                <p class="party-line"><span class="muted">Dirección:</span> {{ $company['address'] }}</p>
                <p class="party-line"><span class="muted">Teléfono:</span> {{ $company['phone'] }}</p>
                @if ($branch)
                    <p class="party-line"><span class="muted">Sede del contrato:</span> {{ $branch->name }} · {{ $branch->city }}</p>
                @endif
            </td>
            <td>
                <p class="section">Cliente / receptor</p>
                <p class="party-name">{{ $client->name }}</p>
                <p class="party-line"><span class="muted">Correo:</span> {{ $client->email }}</p>
                @if ($client->national_id)
                    <p class="party-line"><span class="muted">Cédula / ID:</span> {{ $client->national_id }}</p>
                @endif
                @if ($client->phone)
                    <p class="party-line"><span class="muted">Teléfono:</span> {{ $client->phone }}</p>
                @endif
                <p class="party-line"><span class="muted">Contrato:</span> <strong>{{ $contract->contract_number }}</strong></p>
            </td>
        </tr>
    </table>

    <div class="status-row rule">
        Estado: <span class="status-text red">{{ $status }}</span>
        &nbsp;·&nbsp; Referencia: <strong>{{ $voucher->reference ?: 'Sin referencia' }}</strong>
        &nbsp;·&nbsp; Tasa: <strong>{{ $voucher->frozenRateLabel() }}</strong>
        @if ($voucher->frozenRateWhenLabel() !== '—')
            <span class="muted">({{ $voucher->frozenRateWhenLabel() }})</span>
        @endif
    </div>

    <div class="pad">
        <p class="block-title">Detalle del pago / cuota</p>
        <table class="w100 items">
            <thead>
                <tr>
                    <th style="width:8%">Ítem</th>
                    <th>Descripción</th>
                    <th class="ctr" style="width:12%">Cuota</th>
                    <th class="num" style="width:16%">Monto USD</th>
                    <th class="num" style="width:18%">Monto Bs</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="ctr">1</td>
                    <td>
                        <strong>Cuota de financiamiento</strong><br>
                        <span class="muted">
                            {{ $motorcycle?->name ?? 'Motocicleta' }}
                            @if ($motorcycle)
                                · {{ $motorcycle->displacement }}CC · {{ $motorcycle->lineLabel() }}
                            @endif
                            <br>Contrato {{ $contract->contract_number }} · Plazo {{ $contract->term_months }} meses
                        </span>
                    </td>
                    <td class="ctr">#{{ $installment->number }}/{{ $contract->term_months }}</td>
                    <td class="num">${{ number_format($amountUsd, 2, '.', ',') }}</td>
                    <td class="num">Bs. {{ number_format($amountVes, 2, '.', ',') }}</td>
                </tr>
            </tbody>
        </table>

        @if ($contract->hasAssignedUnit())
            <div class="unit">
                <strong>Unidad asignada:</strong>
                Cartel {{ $contract->unit_cartel }}
                · Placa {{ $contract->unit_plate }}
                · Serial {{ $contract->unit_serial }}
            </div>
        @endif

        <table class="w100" style="margin-top:10px;">
            <tr>
                <td style="width:58%; padding-right:12px; font-size:9px; color:#555555;">
                    <strong style="color:#1a1a1a;">Observaciones</strong><br>
                    Comprobante generado por Motokadi / Kadi Motors para la cuota indicada del contrato de financiamiento.
                    @if ($voucher->original_name)
                        <br>Soporte: {{ $voucher->original_name }}
                    @endif
                    @if ($voucher->reviewed_at)
                        <br>Revisado: {{ $voucher->reviewed_at->format('d/m/Y H:i') }}@if($voucher->reviewer) · {{ $voucher->reviewer->name }}@endif
                    @endif
                    @if ($voucher->review_note)
                        <br>Nota: {{ $voucher->review_note }}
                    @endif
                </td>
                <td style="width:42%;">
                    <table class="w100 totals">
                        <tr>
                            <th>Subtotal USD</th>
                            <td>${{ number_format($amountUsd, 2, '.', ',') }}</td>
                        </tr>
                        <tr>
                            <th>Equivalente Bs</th>
                            <td>Bs. {{ number_format($amountVes, 2, '.', ',') }}</td>
                        </tr>
                        <tr class="grand">
                            <th>Total a consignar</th>
                            <td>${{ number_format($amountUsd, 2, '.', ',') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="stripe" style="height:3px;">&nbsp;</div>
    <table class="w100 footer">
        <tr>
            <td style="width:75%; padding-right:10px;">
                Documento informativo de pago de cuota. Emisor {{ $company['legal_name'] }} · RIF {{ $company['rif'] }}.
                Conservar junto al soporte de transferencia. No sustituye factura fiscal SENIAT si el régimen lo exige por separado.
            </td>
            <td style="width:25%;" class="stamp">
                {{ $status }}<br>
                <span style="font-weight:normal; font-size:8px;">Kadi Motors</span>
            </td>
        </tr>
    </table>
</body>
</html>
