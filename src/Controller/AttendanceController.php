<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Form\AttendanceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AttendanceController extends AbstractController
{
    #[Route('/admin/attendance/create', name: 'attendance_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $attendance = new Attendance();

        $form = $this->createForm(AttendanceType::class, $attendance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 🔥 حماية إضافية
            if (!$attendance->getStudent()) {
                $this->addFlash('error', 'يجب اختيار الطالب');
                return $this->redirectToRoute('attendance_create');
            }

            $attendance->setDate(new \DateTime());

            $em->persist($attendance);
            $em->flush();

            return $this->redirectToRoute('attendance_create');
        }

        return $this->render('attendance/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
