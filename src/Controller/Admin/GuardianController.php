<?php

namespace App\Controller\Admin;

use App\Entity\Guardian;
use App\Form\GuardianType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GuardianController extends AbstractController
{
    #[Route('/admin/guardians', name: 'guardian_list')]
    public function index(EntityManagerInterface $em): Response
    {
        $guardians = $em->getRepository(Guardian::class)->findAll();

        return $this->render('guardian/index.html.twig', [
            'guardians' => $guardians
        ]);
    }

    #[Route('/admin/guardians/create', name: 'guardian_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $guardian = new Guardian();

        $form = $this->createForm(GuardianType::class, $guardian);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($guardian);
            $em->flush();

            return $this->redirectToRoute('guardian_list');
        }

        return $this->render('guardian/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
