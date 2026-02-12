<?php

namespace App\Repository;

use App\Entity\Favorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour l'entité Favorite
 * 
 * Un Repository est une classe qui gère les requêtes à la base de données
 * pour une entité spécifique. Il contient toutes les méthodes pour :
 * - Récupérer des favoris (SELECT)
 * - Sauvegarder des favoris (INSERT/UPDATE)
 * - Supprimer des favoris (DELETE)
 * 
 * @extends ServiceEntityRepository<Favorite>
 */
class FavoriteRepository extends ServiceEntityRepository
{
    /**
     * Constructeur du repository
     * 
     * @param ManagerRegistry $registry Gestionnaire de connexions à la base de données
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favorite::class);
    }

    /**
     * Récupère tous les favoris triés par date d'ajout (plus récent en premier)
     * 
     * Utilise le QueryBuilder de Doctrine pour construire la requête SQL :
     * SELECT * FROM favorite ORDER BY added_at DESC
     * 
     * @return Favorite[] Tableau de tous les favoris
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('f') // 'f' est l'alias pour Favorite
            ->orderBy('f.addedAt', 'DESC') // Tri décroissant par date
            ->getQuery() // Construit la requête
            ->getResult(); // Exécute et retourne les résultats
    }

    /**
     * Recherche un favori par ses coordonnées géographiques
     * 
     * Requête SQL générée :
     * SELECT * FROM favorite WHERE latitude = ? AND longitude = ? LIMIT 1
     * 
     * @param float $latitude La latitude à rechercher
     * @param float $longitude La longitude à rechercher
     * @return Favorite|null Le favori trouvé ou null si aucun
     */
    public function findByCoordinates(float $latitude, float $longitude): ?Favorite
    {
        return $this->createQueryBuilder('f')
            ->where('f.latitude = :lat') // Condition WHERE
            ->andWhere('f.longitude = :lon') // Condition AND
            ->setParameter('lat', $latitude) // Remplace :lat par la valeur (protection SQL injection)
            ->setParameter('lon', $longitude) // Remplace :lon par la valeur
            ->getQuery()
            ->getOneOrNullResult(); // Retourne 1 résultat ou null
    }

    /**
     * Sauvegarde un favori en base de données
     * 
     * persist() : Prépare l'entité pour la sauvegarde (en mémoire)
     * flush() : Exécute réellement la requête SQL INSERT ou UPDATE
     * 
     * @param Favorite $entity Le favori à sauvegarder
     * @param bool $flush Si true, exécute immédiatement la sauvegarde en base
     */
    public function save(Favorite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity); // Prépare la sauvegarde

        if ($flush) {
            $this->getEntityManager()->flush(); // Exécute la sauvegarde
        }
    }

    /**
     * Supprime un favori de la base de données
     * 
     * remove() : Prépare la suppression (en mémoire)
     * flush() : Exécute réellement la requête SQL DELETE
     * 
     * @param Favorite $entity Le favori à supprimer
     * @param bool $flush Si true, exécute immédiatement la suppression
     */
    public function remove(Favorite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity); // Prépare la suppression

        if ($flush) {
            $this->getEntityManager()->flush(); // Exécute la suppression
        }
    }
}
