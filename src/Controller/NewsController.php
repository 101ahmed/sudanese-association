<?php

namespace App\Controller;

use App\Entity\News;
use App\Form\NewsType;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class NewsController extends AbstractController
{
    #[Route('/news', name: 'news')]
    public function index(NewsRepository $newsRepository)
    {
        return $this->render('news/index.html.twig', [
            'news' => $newsRepository->findAll()
        ]);
    }

    // ✅ CREATE
    #[Route('/admin/news/create', name: 'news_create')]
    public function create(Request $request, EntityManagerInterface $em)
    {
        $news = new News();

        $form = $this->createForm(NewsType::class, $news);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $news->setCreatedAt(new \DateTime());

            $em->persist($news);
            $em->flush();

            return $this->redirectToRoute('news');
        }

        return $this->render('news/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    // ✅ EDIT
    #[Route('/admin/news/{id}/edit', name: 'news_edit')]
    public function edit(News $news, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(NewsType::class, $news);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            return $this->redirectToRoute('news');
        }

        return $this->render('news/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    // ✅ DELETE
    #[Route('/admin/news/{id}/delete', name: 'news_delete')]
    public function delete(News $news, EntityManagerInterface $em)
    {
        $em->remove($news);
        $em->flush();

        return $this->redirectToRoute('news');
    }
}
