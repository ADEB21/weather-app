<?php

namespace App\Entity;

use App\Repository\SearchHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité SearchHistory - Représente une recherche dans l'historique
 * 
 * Stocke chaque recherche de ville effectuée par l'utilisateur.
 * Un index est créé sur (latitude, longitude, searched_at) pour optimiser les requêtes.
 * 
 * Table : search_history
 * Index : idx_location_date pour accélérer les recherches par localisation et date
 */
#[ORM\Entity(repositoryClass: SearchHistoryRepository::class)]
#[ORM\Index(columns: ['latitude', 'longitude', 'searched_at'], name: 'idx_location_date')]
class SearchHistory
{
    /**
     * Identifiant unique de la recherche
     * 
     * @var int|null Généré automatiquement
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom de la ville recherchée
     * 
     * @var string|null Maximum 255 caractères
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    /**
     * Code pays de la ville
     * 
     * @var string|null 2 caractères (ex: "FR")
     */
    #[ORM\Column(length: 2, nullable: true)]
    private ?string $country = null;

    /**
     * Latitude de la ville recherchée
     * 
     * @var float Coordonnée géographique
     */
    #[ORM\Column]
    private float $latitude;

    /**
     * Longitude de la ville recherchée
     * 
     * @var float Coordonnée géographique
     */
    #[ORM\Column]
    private float $longitude;

    /**
     * Date et heure de la recherche
     * 
     * @var \DateTimeImmutable Horodatage de la recherche
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $searchedAt;

    /**
     * Constructeur - Initialise la date de recherche à maintenant
     */
    public function __construct()
    {
        $this->searchedAt = new \DateTimeImmutable();
    }

    /** @return int|null */
    public function getId(): ?int
    {
        return $this->id;
    }

    /** @return string|null */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /** @param string|null $city */
    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    /** @return string|null */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /** @param string|null $country */
    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    /** @return float */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /** @param float $latitude */
    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    /** @return float */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /** @param float $longitude */
    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    /** @return \DateTimeImmutable */
    public function getSearchedAt(): \DateTimeImmutable
    {
        return $this->searchedAt;
    }

    /** @param \DateTimeImmutable $searchedAt */
    public function setSearchedAt(\DateTimeImmutable $searchedAt): static
    {
        $this->searchedAt = $searchedAt;
        return $this;
    }
}
