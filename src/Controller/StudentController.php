<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StudentController extends AbstractController
{
    #[Route('/admin/students', name: 'student_list')]
    public function index(EntityManagerInterface $em): Response
    {
        $students = $em->getRepository(Student::class)->findAll();

        return $this->render('student/index.html.twig', [
            'students' => $students,
        ]);
    }

    #[Route('/admin/students/create', name: 'student_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $student = new Student();

        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $student->setCreatedAt(new \DateTime()); // 🔥 الحل

            $em->persist($student);
            $em->flush();

            return $this->redirectToRoute('student_list');
        }

        return $this->render('student/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
