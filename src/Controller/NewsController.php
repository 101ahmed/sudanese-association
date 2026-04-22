<?php

namespace App\Controller;

use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    #[Route('/news', name: 'public_news_list')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $q = $request->query->get('q');
        $qb = $em->getRepository(News::class)->createQueryBuilder('n')
            ->orderBy('n.id', 'DESC');

        if (is_string($q) && trim($q) !== '') {
            $term = '%'.trim($q).'%';
            $qb->andWhere('n.title LIKE :q OR n.content LIKE :q')
                ->setParameter('q', $term);
        }

        $newsList = $qb->getQuery()->getResult();

        return $this->render('news/index.html.twig', [
            'newsList' => $newsList,
            'searchQuery' => is_string($q) ? $q : '',
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

