<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AcademicController extends AbstractController
{
    #[Route('/admin/academic', name: 'academic_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/academic/index.html.twig');
    }
}
