<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EventController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/events', name: 'events')]
    public function index(Request $request): Response
    {

        $city = $request->query->get('city', '');
        $date = $request->query->get('date', '');
        $events = [];

        if ($city && $date) {
            $apiUrl = sprintf(
                'https://public.opendatasoft.com/api/records/1.0/search/?dataset=evenements-publics-openagenda&refine.location_city=%s&refine.firstdate_begin=%s',
                urlencode($city),
                urlencode($date)
            );

            $response = $this->httpClient->request('GET', $apiUrl);

            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                $events = $data['records'] ?? [];
            }
        }

        return $this->render('events/index.html.twig', [
            'events' => $events,
            'city' => $city,
            'date' => $date,
        ]);
    }
}
