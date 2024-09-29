<?php

declare(strict_types=1);

namespace Tests;

use App\CommissionCalculator;
use App\Exceptions\JsonParseException;
use App\Services\BinListProvider;
use App\Services\ExchangeRatesApiProvider;
use JsonException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
	/**
	 * @throws Exception
	 * @throws JsonException|JsonParseException
	 */
	public function testCalculate(): void
	{
		$binProviderMock = $this->createMock(BinListProvider::class);
		$currencyRateProviderMock = $this->createMock(ExchangeRatesApiProvider::class);

		$binProviderMock->method('getBinData')->willReturnOnConsecutiveCalls(
			['country' => ['alpha2' => 'DK']],
			['country' => ['alpha2' => 'LT']],
			['country' => ['alpha2' => 'JP']],
			['country' => ['alpha2' => 'US']],
			['country' => ['alpha2' => 'GB']]
		);

		$currencyRateProviderMock->method('getRate')->willReturnMap([
			['EUR', 1.0],
			['USD', 1.1497],
			['JPY', 129.53],
			['GBP', 0.8569],
		]);

		$calculator = new CommissionCalculator($binProviderMock, $currencyRateProviderMock);

		$this->expectOutputString("1\n0.44\n1.55\n2.26\n46.68\n");

		$calculator->calculate(__DIR__.'/input.txt');
	}
}
