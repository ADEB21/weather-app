<?php

namespace App\Repository;

use App\Entity\Favorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Favorite>
 */
class FavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favorite::class);
    }

    /**
     * @return Favorite[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.addedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCoordinates(float $latitude, float $longitude): ?Favorite
    {
        return $this->createQueryBuilder('f')
            ->where('f.latitude = :lat')
            ->andWhere('f.longitude = :lon')
            ->setParameter('lat', $latitude)
            ->setParameter('lon', $longitude)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Favorite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Favorite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
