<?php

namespace App\Services\Financing;

use InvalidArgumentException;

final class InstallmentSimulator
{
    public const MONTHS = 9;

    /**
     * Saldo = precio − inicial.
     * La cuota mensual trunca el saldo entre los meses, sin redondear el centavo hacia arriba.
     *
     * @return array{price: string, initial: string, balance: string, monthly: string, months: int}
     */
    public function quote(float|int|string $price, float|int|string $initial, int $months = self::MONTHS): array
    {
        $priceCents = $this->toCents($price);
        $initialCents = $this->toCents($initial);

        if ($months < 1) {
            throw new InvalidArgumentException('El plazo debe ser de al menos un mes.');
        }

        if ($initialCents < 0 || $initialCents > $priceCents) {
            throw new InvalidArgumentException('La inicial debe estar entre 0 y el precio de la moto.');
        }

        $balanceCents = $priceCents - $initialCents;

        return [
            'price' => $this->fromCents($priceCents),
            'initial' => $this->fromCents($initialCents),
            'balance' => $this->fromCents($balanceCents),
            'monthly' => $this->fromCents(intdiv($balanceCents, $months)),
            'months' => $months,
        ];
    }

    private function toCents(float|int|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
