<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Country;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;

final class CountrySyncService
{
    private const API_URL = 'https://restcountries.com/v3.1/all?fields=cca2,name,region,subregion,demonyms,population,independent,flags,currencies';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CountryRepository $countryRepository,
    ) {
    }

    public function sync(): void
    {
        $apiCountries = $this->fetchFromApi();
        $existingUuids = $this->countryRepository->findAllUuids();
        $apiUuids = array_keys($apiCountries);

        foreach ($apiCountries as $cca2 => $data) {
            $country = $this->countryRepository->find($cca2);
            if ($country === null) {
                $country = new Country();
                $country->setUuid($cca2);
            }
            $this->mapApiDataToEntity($data, $country);
            $this->em->persist($country);
        }

        foreach ($existingUuids as $uuid) {
            if (!\in_array($uuid, $apiUuids, true)) {
                $country = $this->countryRepository->find($uuid);
                if ($country !== null) {
                    $this->em->remove($country);
                }
            }
        }

        $this->em->flush();
    }

    private function fetchFromApi(): array
    {
        $json = file_get_contents(self::API_URL);
        if ($json === false) {
            throw new \RuntimeException('Failed to fetch countries from REST Countries API');
        }
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $byCca2 = [];
        foreach ($decoded as $row) {
            $cca2 = $row['cca2'] ?? null;
            if ($cca2 !== null && $cca2 !== '') {
                $byCca2[$cca2] = $row;
            }
        }
        return $byCca2;
    }

    private function mapApiDataToEntity(array $data, Country $country): void
    {
        $country->setName($data['name']['common'] ?? '');
        $country->setRegion($data['region'] ?? '');
        $country->setSubRegion($data['subregion'] ?? '');
        $country->setDemonym($this->extractDemonym($data));
        $country->setPopulation((int) ($data['population'] ?? 0));
        $country->setIndependent((bool) ($data['independent'] ?? false));
        $country->setFlag($data['flags']['png'] ?? $data['flags']['svg'] ?? '');

        $currencyName = null;
        $currencySymbol = null;
        if (!empty($data['currencies']) && \is_array($data['currencies'])) {
            $first = reset($data['currencies']);
            if (\is_array($first)) {
                $currencyName = $first['name'] ?? null;
                $currencySymbol = $first['symbol'] ?? null;
            }
        }
        $country->setCurrencyName($currencyName);
        $country->setCurrencySymbol($currencySymbol);
    }

    private function extractDemonym(array $data): string
    {
        $demonyms = $data['demonyms'] ?? [];
        if (\is_array($demonyms) && isset($demonyms['eng']['m'])) {
            return (string) $demonyms['eng']['m'];
        }
        if (\is_array($demonyms) && isset($demonyms['eng']['f'])) {
            return (string) $demonyms['eng']['f'];
        }
        return '';
    }
}
