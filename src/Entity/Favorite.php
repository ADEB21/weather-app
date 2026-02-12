<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité Favorite - Représente une ville favorite en base de données
 * 
 * Une entité est une classe PHP qui représente une table dans la base de données.
 * Chaque propriété correspond à une colonne de la table.
 * 
 * Table : favorite
 * Contrainte : Une seule entrée par couple (latitude, longitude)
 */
#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_location', columns: ['latitude', 'longitude'])]
class Favorite
{
    /**
     * Identifiant unique du favori
     * 
     * @var int|null Généré automatiquement par la base de données
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom de la ville
     * 
     * @var string|null Maximum 255 caractères, peut être null
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    /**
     * Code pays (ISO 3166-1 alpha-2)
     * 
     * @var string|null 2 caractères (ex: "FR", "US"), peut être null
     */
    #[ORM\Column(length: 2, nullable: true)]
    private ?string $country = null;

    /**
     * Latitude de la localisation
     * 
     * @var float Coordonnée géographique (ex: 48.8566 pour Paris)
     */
    #[ORM\Column]
    private float $latitude;

    /**
     * Longitude de la localisation
     * 
     * @var float Coordonnée géographique (ex: 2.3522 pour Paris)
     */
    #[ORM\Column]
    private float $longitude;

    /**
     * Date et heure d'ajout du favori
     * 
     * @var \DateTimeImmutable Immutable = ne peut pas être modifié après création
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $addedAt;

    /**
     * Constructeur - Appelé lors de la création d'un nouveau favori
     * 
     * Initialise automatiquement la date d'ajout à maintenant
     */
    public function __construct()
    {
        $this->addedAt = new \DateTimeImmutable();
    }

    /**
     * Récupère l'ID du favori
     * 
     * @return int|null L'identifiant ou null si pas encore enregistré en base
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère le nom de la ville
     * 
     * @return string|null Le nom de la ville ou null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Définit le nom de la ville
     * 
     * @param string|null $city Le nom de la ville
     * @return static Retourne l'objet lui-même pour permettre le chaînage (fluent interface)
     */
    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this; // Permet d'écrire : $favorite->setCity('Paris')->setCountry('FR')
    }

    /**
     * Récupère le code pays
     * 
     * @return string|null Le code pays (2 lettres) ou null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Définit le code pays
     * 
     * @param string|null $country Le code pays (ex: "FR")
     * @return static Retourne l'objet pour le chaînage
     */
    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Récupère la latitude
     * 
     * @return float La coordonnée latitude
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * Définit la latitude
     * 
     * @param float $latitude La coordonnée latitude
     * @return static Retourne l'objet pour le chaînage
     */
    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Récupère la longitude
     * 
     * @return float La coordonnée longitude
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * Définit la longitude
     * 
     * @param float $longitude La coordonnée longitude
     * @return static Retourne l'objet pour le chaînage
     */
    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Récupère la date d'ajout du favori
     * 
     * @return \DateTimeImmutable La date et heure d'ajout
     */
    public function getAddedAt(): \DateTimeImmutable
    {
        return $this->addedAt;
    }

    /**
     * Définit la date d'ajout du favori
     * 
     * @param \DateTimeImmutable $addedAt La date et heure d'ajout
     * @return static Retourne l'objet pour le chaînage
     */
    public function setAddedAt(\DateTimeImmutable $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }
}
