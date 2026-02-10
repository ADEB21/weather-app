<?php

namespace App\Repository;

use App\Entity\SearchHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SearchHistory>
 */
class SearchHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchHistory::class);
    }

    /**
     * @return SearchHistory[]
     */
    public function findRecentSearches(int $limit = 10): array
    {
        return $this->createQueryBuilder('sh')
            ->orderBy('sh.searchedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return SearchHistory[]
     */
    public function findUniqueRecentSearches(int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('sh')
            ->select('sh')
            ->groupBy('sh.latitude', 'sh.longitude')
            ->orderBy('MAX(sh.searchedAt)', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function save(SearchHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SearchHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
