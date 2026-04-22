<?php

namespace App\Controller\Admin;

use App\Entity\News;
use App\Form\NewsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class NewsController extends AbstractController
{
    #[Route('/admin/news', name: 'news_list')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('q');

        $qb = $em->createQueryBuilder()
            ->select('n')
            ->from(News::class, 'n')
            ->orderBy('n.id', 'DESC');

        if ($search) {
            $qb->andWhere('n.title LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        $news = $qb->getQuery()->getResult();

        return $this->render('admin/news/index.html.twig', [
            'news' => $news
        ]);
    }

    #[Route('/admin/news/create', name: 'news_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $news = new News();
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $news->setCreatedAt(new \DateTime());

            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $ext = $imageFile->guessExtension() ?: 'jpg';
                $fileName = uniqid('news_', true) . '.' . $ext;
                $imageFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads',
                    $fileName
                );
                $news->setImage($fileName);
            }

            $em->persist($news);
            $em->flush();

            return $this->redirectToRoute('news_list');
        }

        return $this->render('admin/news/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/news/{id}', name: 'news_show')]
    public function show(News $news): Response
    {
        return $this->render('admin/news/show.html.twig', [
            'news' => $news
        ]);
    }

    #[Route('/admin/news/{id}/edit', name: 'news_edit')]
    public function edit(News $news, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $ext = $imageFile->guessExtension() ?: 'jpg';
                $fileName = uniqid('news_', true) . '.' . $ext;
                $imageFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads',
                    $fileName
                );
                $news->setImage($fileName);
            }

            $em->flush();

            return $this->redirectToRoute('news_list');
        }

        return $this->render('admin/news/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/news/{id}/delete', name: 'news_delete')]
    public function delete(News $news, EntityManagerInterface $em): Response
    {
        $em->remove($news);
        $em->flush();

        return $this->redirectToRoute('news_list');
    }
}
