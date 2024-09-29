<?php

declare(strict_types=1);

namespace App\Services;

interface CurrencyRateProviderInterface
{
	public function getRate(string $currency): float;
}
