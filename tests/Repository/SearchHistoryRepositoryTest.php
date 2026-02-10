<?php

namespace App\Tests\Repository;

use App\Entity\SearchHistory;
use App\Repository\SearchHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SearchHistoryRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private SearchHistoryRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->repository = $this->entityManager->getRepository(SearchHistory::class);

        $this->entityManager->createQuery('DELETE FROM App\Entity\SearchHistory')->execute();
    }

    public function testSaveSearchHistory(): void
    {
        $search = new SearchHistory();
        $search->setCity('Paris');
        $search->setCountry('FR');
        $search->setLatitude(48.8566);
        $search->setLongitude(2.3522);

        $this->repository->save($search, true);

        $this->assertNotNull($search->getId());
        $this->assertEquals('Paris', $search->getCity());
    }

    public function testFindRecentSearches(): void
    {
        $paris = new SearchHistory();
        $paris->setCity('Paris')->setLatitude(48.8566)->setLongitude(2.3522);
        $this->repository->save($paris, true);

        sleep(1);

        $lyon = new SearchHistory();
        $lyon->setCity('Lyon')->setLatitude(45.7640)->setLongitude(4.8357);
        $this->repository->save($lyon, true);

        $results = $this->repository->findRecentSearches(10);

        $this->assertCount(2, $results);
        $this->assertEquals('Lyon', $results[0]->getCity());
        $this->assertEquals('Paris', $results[1]->getCity());
    }

    public function testFindUniqueRecentSearches(): void
    {
        $paris1 = new SearchHistory();
        $paris1->setCity('Paris')->setLatitude(48.8566)->setLongitude(2.3522);
        $this->repository->save($paris1, true);

        sleep(1);

        $paris2 = new SearchHistory();
        $paris2->setCity('Paris')->setLatitude(48.8566)->setLongitude(2.3522);
        $this->repository->save($paris2, true);

        $allResults = $this->repository->findRecentSearches(10);
        $this->assertCount(2, $allResults);

        $uniqueResults = $this->repository->findUniqueRecentSearches(10);
        $this->assertCount(1, $uniqueResults);
        $this->assertEquals('Paris', $uniqueResults[0]->getCity());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
    }
}
