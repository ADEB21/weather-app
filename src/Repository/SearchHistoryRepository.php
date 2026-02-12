<?php

namespace App\Repository;

use App\Entity\SearchHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour l'entité SearchHistory
 * 
 * Gère toutes les requêtes liées à l'historique des recherches.
 * Permet de récupérer les recherches récentes, uniques, etc.
 * 
 * @extends ServiceEntityRepository<SearchHistory>
 */
class SearchHistoryRepository extends ServiceEntityRepository
{
    /**
     * Constructeur du repository
     * 
     * @param ManagerRegistry $registry Gestionnaire de connexions à la base de données
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchHistory::class);
    }

    /**
     * Récupère les recherches récentes (toutes, même les doublons)
     * 
     * Requête SQL : SELECT * FROM search_history ORDER BY searched_at DESC LIMIT ?
     * 
     * @param int $limit Nombre maximum de résultats (10 par défaut)
     * @return SearchHistory[] Tableau des recherches récentes
     */
    public function findRecentSearches(int $limit = 10): array
    {
        return $this->createQueryBuilder('sh') // 'sh' = alias pour SearchHistory
            ->orderBy('sh.searchedAt', 'DESC') // Tri par date décroissante
            ->setMaxResults($limit) // Limite le nombre de résultats
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les recherches récentes UNIQUES (sans doublons de localisation)
     * 
     * Utilise GROUP BY pour ne garder qu'une seule recherche par localisation.
     * Garde la plus récente pour chaque couple (latitude, longitude).
     * 
     * Requête SQL :
     * SELECT * FROM search_history 
     * GROUP BY latitude, longitude 
     * ORDER BY MAX(searched_at) DESC 
     * LIMIT ?
     * 
     * @param int $limit Nombre maximum de résultats (10 par défaut)
     * @return SearchHistory[] Tableau des recherches uniques
     */
    public function findUniqueRecentSearches(int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('sh')
            ->select('sh')
            ->groupBy('sh.latitude', 'sh.longitude') // Regroupe par localisation
            ->orderBy('MAX(sh.searchedAt)', 'DESC') // Tri par date la plus récente du groupe
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Sauvegarde une recherche en base de données
     * 
     * @param SearchHistory $entity La recherche à sauvegarder
     * @param bool $flush Si true, exécute immédiatement la sauvegarde
     */
    public function save(SearchHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime une recherche de la base de données
     * 
     * @param SearchHistory $entity La recherche à supprimer
     * @param bool $flush Si true, exécute immédiatement la suppression
     */
    public function remove(SearchHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
