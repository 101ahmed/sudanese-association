<?php

namespace App\Controller;

use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    #[Route('/news', name: 'public_news_list')]
    public function index(EntityManagerInterface $em): Response
    {
        $newsList = $em->getRepository(News::class)->findBy([], ['id' => 'DESC']);

        return $this->render('news/index.html.twig', [
            'newsList' => $newsList,
        ]);
    }

    #[Route('/news/{id}', name: 'public_news_show', requirements: ['id' => '\d+'])]
    public function show(News $news): Response
    {
        return $this->render('news/show.html.twig', [
            'news' => $news,
        ]);
    }
}

