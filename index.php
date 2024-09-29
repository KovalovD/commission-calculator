<?php

require __DIR__.'/vendor/autoload.php';

use App\CommissionCalculator;
use App\Services\BinListProvider;
use App\Services\ExchangeRatesApiProvider;
use GuzzleHttp\Client;

if ($argc !== 2) {
	echo "Usage: php index.php <input_file_path>" . PHP_EOL;
	exit(1);
}

$inputFile = $argv[1];

$httpClient = new Client();
$binProvider = new BinListProvider($httpClient);
$currencyRateProvider = new ExchangeRatesApiProvider($httpClient);

$calculator = new CommissionCalculator($binProvider, $currencyRateProvider);
$calculator->calculate($inputFile);
