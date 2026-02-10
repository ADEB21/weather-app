<?php

namespace App\Entity;

use App\Repository\SearchHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SearchHistoryRepository::class)]
#[ORM\Index(columns: ['latitude', 'longitude', 'searched_at'], name: 'idx_location_date')]
class SearchHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $country = null;

    #[ORM\Column]
    private float $latitude;

    #[ORM\Column]
    private float $longitude;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $searchedAt;

    public function __construct()
    {
        $this->searchedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getSearchedAt(): \DateTimeImmutable
    {
        return $this->searchedAt;
    }

    public function setSearchedAt(\DateTimeImmutable $searchedAt): static
    {
        $this->searchedAt = $searchedAt;

        return $this;
    }
}
