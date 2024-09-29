<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

class BinListProvider implements BinProviderInterface
{
	private ClientInterface $client;
	private string $apiUrl = 'https://lookup.binlist.net/';

	public function __construct(ClientInterface $client)
	{
		$this->client = $client;
	}

	/**
	 * @throws JsonException
	 */
	public function getBinData(string $bin): array
	{
		try {
			$response = $this->client->request('GET', $this->apiUrl . $bin);
			return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
		} catch (GuzzleException $e) {
			return [];
		}
	}
}
