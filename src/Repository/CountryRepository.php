<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    public function findAllUuids(): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('c.uuid')
            ->getQuery()
            ->getSingleColumnResult();
        return array_values($result);
    }
}
