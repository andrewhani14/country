<?php
declare(strict_types=1);

namespace App\Controller\V1;

use App\Entity\Country;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Countries')]
#[Route('/countries')]
class CountryController extends AbstractController
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/list', name: 'api_countries_list', methods: ['GET'])]
    #[OA\Get(path: '/api/v1/countries/list', summary: 'List all countries', tags: ['Countries'])]
    #[OA\Response(response: 200, description: 'List of countries')]
    public function list(): JsonResponse
    {
        $countries = $this->countryRepository->findBy([], ['name' => 'ASC']);
        $data = array_map(static fn (Country $c) => $c->toArray(), $countries);
        return new JsonResponse($data);
    }

    #[Route('/{uuid}', name: 'api_country_get', methods: ['GET'], requirements: ['uuid' => '[A-Za-z]{2}'])]
    #[OA\Get(path: '/api/v1/countries/{uuid}', summary: 'Get a country by code (e.g. DE, FR)', tags: ['Countries'])]
    #[OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'DE'))]
    #[OA\Response(response: 200, description: 'Country data')]
    #[OA\Response(response: 404, description: 'Country not found')]
    public function get(string $uuid): JsonResponse
    {
        $country = $this->countryRepository->find($uuid);
        if ($country === null) {
            return new JsonResponse(['error' => 'Country not found'], 404);
        }
        return new JsonResponse($country->toArray());
    }

    #[Route('', name: 'api_country_post', methods: ['POST'])]
    #[OA\Post(path: '/api/v1/countries', summary: 'Create a country (requires Basic Auth)', tags: ['Countries'], security: [['httpBasic' => []]])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['uuid'],
        properties: [
            new OA\Property(property: 'uuid', type: 'string', example: 'XX'),
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'region', type: 'string'),
            new OA\Property(property: 'subRegion', type: 'string'),
            new OA\Property(property: 'demonym', type: 'string'),
            new OA\Property(property: 'population', type: 'integer'),
            new OA\Property(property: 'independent', type: 'boolean'),
            new OA\Property(property: 'flag', type: 'string'),
            new OA\Property(property: 'currency', properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'symbol', type: 'string'),
            ], type: 'object'),
        ]
    ))]
    #[OA\Response(response: 201, description: 'Country created')]
    #[OA\Response(response: 400, description: 'Invalid input')]
    #[OA\Response(response: 401, description: 'Authentication required')]
    #[OA\Response(response: 409, description: 'Country already exists')]
    public function post(Request $request): JsonResponse
    {
        $body = $request->toArray();
        $uuid = $body['uuid'] ?? null;
        if ($uuid === null || $uuid === '') {
            return new JsonResponse(['error' => 'uuid is required'], 400);
        }
        if (\strlen($uuid) !== 2) {
            return new JsonResponse(['error' => 'uuid must be a 2-character country code'], 400);
        }
        if ($this->countryRepository->find($uuid) !== null) {
            return new JsonResponse(['error' => 'Country already exists'], 409);
        }
        $country = new Country();
        $country->setUuid($uuid);
        $this->applyBodyToCountry($body, $country);
        $this->em->persist($country);
        $this->em->flush();
        return new JsonResponse($country->toArray(), 201);
    }

    #[Route('/{uuid}', name: 'api_country_patch', methods: ['PATCH'], requirements: ['uuid' => '[A-Za-z]{2}'])]
    #[OA\Patch(path: '/api/v1/countries/{uuid}', summary: 'Update a country (requires Basic Auth)', tags: ['Countries'], security: [['httpBasic' => []]])]
    #[OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'DE'))]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'region', type: 'string'),
        new OA\Property(property: 'subRegion', type: 'string'),
        new OA\Property(property: 'demonym', type: 'string'),
        new OA\Property(property: 'population', type: 'integer'),
        new OA\Property(property: 'independent', type: 'boolean'),
        new OA\Property(property: 'flag', type: 'string'),
        new OA\Property(property: 'currency', properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'symbol', type: 'string'),
        ], type: 'object'),
    ]))]
    #[OA\Response(response: 200, description: 'Country updated')]
    #[OA\Response(response: 401, description: 'Authentication required')]
    #[OA\Response(response: 404, description: 'Country not found')]
    public function patch(string $uuid, Request $request): JsonResponse
    {
        $country = $this->countryRepository->find($uuid);
        if ($country === null) {
            return new JsonResponse(['error' => 'Country not found'], 404);
        }
        $body = $request->toArray();
        $this->applyBodyToCountry($body, $country);
        $this->em->flush();
        return new JsonResponse($country->toArray());
    }

    #[Route('/{uuid}', name: 'api_country_delete', methods: ['DELETE'], requirements: ['uuid' => '[A-Za-z]{2}'])]
    #[OA\Delete(path: '/api/v1/countries/{uuid}', summary: 'Delete a country (requires Basic Auth)', tags: ['Countries'], security: [['httpBasic' => []]])]
    #[OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'DE'))]
    #[OA\Response(response: 204, description: 'Country deleted')]
    #[OA\Response(response: 401, description: 'Authentication required')]
    #[OA\Response(response: 404, description: 'Country not found')]
    public function delete(string $uuid): JsonResponse
    {
        $country = $this->countryRepository->find($uuid);
        if ($country === null) {
            return new JsonResponse(['error' => 'Country not found'], 404);
        }
        $this->em->remove($country);
        $this->em->flush();
        return new JsonResponse(null, 204);
    }

    private function applyBodyToCountry(array $body, Country $country): void
    {
        if (isset($body['name'])) {
            $country->setName((string) $body['name']);
        }
        if (isset($body['region'])) {
            $country->setRegion((string) $body['region']);
        }
        if (isset($body['subRegion'])) {
            $country->setSubRegion((string) $body['subRegion']);
        }
        if (isset($body['demonym'])) {
            $country->setDemonym((string) $body['demonym']);
        }
        if (array_key_exists('population', $body)) {
            $country->setPopulation((int) $body['population']);
        }
        if (array_key_exists('independent', $body)) {
            $country->setIndependent((bool) $body['independent']);
        }
        if (isset($body['flag'])) {
            $country->setFlag((string) $body['flag']);
        }
        if (isset($body['currency']) && \is_array($body['currency'])) {
            $country->setCurrencyName(isset($body['currency']['name']) ? (string) $body['currency']['name'] : null);
            $country->setCurrencySymbol(isset($body['currency']['symbol']) ? (string) $body['currency']['symbol'] : null);
        }
    }
}
