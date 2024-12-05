<?php

namespace App\Helpers;

use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EventApiService
{
    private HttpClientInterface $httpClient;
    private string $apiBaseUrl;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->apiBaseUrl = 'https://public.opendatasoft.com/explore/dataset/evenements-publics-openagenda/api/';
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function getEvents(): array
    {
        $response = $this->httpClient->request('GET', $this->apiBaseUrl . '/events');

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Erreur lors de la récupération des événements');
        }
        return $response->toArray();
    }

}