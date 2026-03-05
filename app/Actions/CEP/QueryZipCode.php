<?php

namespace App\Actions\CEP;

use App\DTOs\OpenCEPResponse;
use GuzzleHttp\Client;

class QueryZipCode
{

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public static function query(string $zipCode): OpenCEPResponse
    {
        $onlyDigitsZipCode = preg_replace('/[^0-9]/', '', $zipCode);
        $queryUrl = "https://opencep.com/v1/{$onlyDigitsZipCode}";

        $client = new Client();
        $response = $client->request('GET', $queryUrl);
        $data = json_decode($response->getBody(), true);

        return new OpenCEPResponse($data);
    }
}
