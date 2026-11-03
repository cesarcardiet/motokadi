<?php

namespace App\Support;

use App\Services\Settings\ExchangeRate;

final class Money
{
    public static function whole(float|int|string|null $amount): int
    {
        return (int) round((float) $amount);
    }

    public static function plain(float|int|string|null $amount): string
    {
        return number_format(self::whole($amount), 0, ',', '.');
    }

    public static function usd(float|int|string|null $amount): string
    {
        return '$'.self::plain($amount).' USD';
    }

    public static function ves(float|int|string|null $amount): string
    {
        return 'Bs. '.self::plain($amount);
    }

    public static function rate(?float $rate = null): string
    {
        $rate ??= app(ExchangeRate::class)->current();

        return self::plain($rate).' Bs = 1 USD';
    }

    public static function both(float|int|string|null $usdAmount, ?float $rate = null): string
    {
        $usd = (float) $usdAmount;
        $rate ??= app(ExchangeRate::class)->current();
        $ves = app(ExchangeRate::class)->toVes($usd, $rate);

        return self::usd($usd).' · '.self::ves($ves);
    }

    public static function bothLines(float|int|string|null $usdAmount, ?float $rate = null): string
    {
        $usd = (float) $usdAmount;
        $rate ??= app(ExchangeRate::class)->current();
        $ves = app(ExchangeRate::class)->toVes($usd, $rate);

        return self::usd($usd).'<br><span class="money-ves">'.self::ves($ves).'</span>';
    }
}
