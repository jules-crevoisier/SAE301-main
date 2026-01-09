<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return Article[] Returns an array of published articles
     */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.published = :published')
            ->setParameter('published', true)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[] Returns an array of articles by category
     */
    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.category = :category')
            ->andWhere('a.published = :published')
            ->setParameter('category', $category)
            ->setParameter('published', true)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
