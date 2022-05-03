<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BallDontLieService
{
    private HttpClientInterface $client;

    const API_URL = "https://www.balldontlie.io/api/v1/";

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function search(string $keyword): string
    {
        // create request
        $response = $this->client->request('GET', self::API_URL . '/players?search=' . $keyword);

        // getting the response headers waits until they arrive
        $contentType = $response->getHeaders()['content-type'][0];

        return $response->getContent();
    }
}
