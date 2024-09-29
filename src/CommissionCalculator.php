<?php

declare(strict_types=1);

namespace App;

use App\Exceptions\JsonParseException;
use App\Services\BinProviderInterface;
use App\Services\CurrencyRateProviderInterface;
use JsonException;
use SplFileObject;

class CommissionCalculator
{
	private BinProviderInterface $binProvider;
	private CurrencyRateProviderInterface $currencyRateProvider;

	public function __construct(
		BinProviderInterface $binProvider,
		CurrencyRateProviderInterface $currencyRateProvider
	) {
		$this->binProvider = $binProvider;
		$this->currencyRateProvider = $currencyRateProvider;
	}

	/**
	 * @throws JsonException
	 * @throws JsonParseException
	 */
	public function calculate(string $filePath): void
	{
		$transactions = $this->parseTransactions($filePath);

		foreach ($transactions as $transaction) {
			$binData = $this->binProvider->getBinData($transaction['bin']);
			$isEu = $this->isEu($binData['country']['alpha2'] ?? '');

			$rate = $this->currencyRateProvider->getRate($transaction['currency']);
			$amountFixed = $this->convertAmount((float)$transaction['amount'], $rate);

			$commissionRate = $isEu ? 0.01 : 0.02;
			$commission = ceil($amountFixed * $commissionRate * 100) / 100;

			echo $commission . PHP_EOL;
		}
	}

	/**
	 * @throws JsonException
	 * @throws JsonParseException
	 */
	private function parseTransactions(string $filePath): array
	{
		$transactions = [];

		$file = new SplFileObject($filePath);
		while (!$file->eof()) {
			$line = trim($file->fgets());
			if ($line) {
				$transaction = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
				if (json_last_error() === JSON_ERROR_NONE) {
					$transactions[] = $transaction;
				} else {
					throw new JsonParseException('JSON parsing error');
				}
			}
		}

		return $transactions;
	}

	private function convertAmount(float $amount, float $rate): float
	{
		return $rate > 0 ? $amount / $rate : $amount;
	}

	private function isEu(string $countryCode): bool
	{
		$euCountries = [
			'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI',
			'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
			'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
		];

		return in_array($countryCode, $euCountries, true);
	}
}
