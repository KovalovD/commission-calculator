<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

class ExchangeRatesApiProvider implements CurrencyRateProviderInterface
{
	private ClientInterface $client;
	private string $apiUrl = 'https://api.exchangerate.host/latest';

	public function __construct(ClientInterface $client)
	{
		$this->client = $client;
	}

	/**
	 * @throws JsonException
	 */
	public function getRate(string $currency): float
	{
		if ($currency === 'EUR') {
			return 1.0;
		}

		try {
			$response = $this->client->request('GET', $this->apiUrl, [
				'query' => ['base' => 'EUR', 'symbols' => $currency],
			]);
			$data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

			return $data['rates'][$currency] ?? 0.0;
		} catch (GuzzleException $e) {
			return 0.0;
		}
	}
}
