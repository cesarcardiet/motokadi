<?php

namespace App\Services\Settings;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

class ExchangeRate
{
    public const KEY = 'usd_ves_rate';

    public const DEFAULT = 1000.0;

    public function current(): float
    {
        return (float) Cache::remember('settings.'.self::KEY, 60, function () {
            return (float) AppSetting::getValue(self::KEY, self::DEFAULT);
        });
    }

    public function set(float $rate): float
    {
        $rate = round(max($rate, 0.0001), 4);
        AppSetting::putValue(self::KEY, number_format($rate, 4, '.', ''));
        Cache::forget('settings.'.self::KEY);

        return $this->current();
    }

    public function toVes(float|int|string|null $usd, ?float $rate = null): float
    {
        $rate ??= $this->current();

        return round((float) $usd * $rate, 2);
    }
}
