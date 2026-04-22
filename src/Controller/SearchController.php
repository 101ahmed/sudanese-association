<?php

namespace App\Controller;

use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search')]
    public function search(Request $request, EntityManagerInterface $em): Response
    {
        $q = $request->query->get('q');

        $news = $em->createQueryBuilder()
            ->select('n')
            ->from(News::class, 'n')
            ->where('n.title LIKE :q')
            ->setParameter('q', '%'.$q.'%')
            ->getQuery()
            ->getResult();

        return $this->json($news);
    }
}
