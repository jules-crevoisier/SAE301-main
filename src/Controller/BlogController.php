<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog')]
    public function index(ArticleRepository $articleRepository, Request $request): Response
    {
        $category = $request->query->get('category', 'all');
        
        if ($category === 'all') {
            $articles = $articleRepository->findPublished();
        } else {
            $articles = $articleRepository->findByCategory($category);
        }
        
        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'selectedCategory' => $category,
        ]);
    }
}
