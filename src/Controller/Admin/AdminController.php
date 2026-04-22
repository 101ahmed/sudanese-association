<?php

namespace App\Controller\Admin;

use App\Entity\Attendance;
use App\Entity\Guardian;
use App\Entity\Student;
use App\Form\StudentWithGuardianType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // 📊 الإحصائيات
        $studentsCount = $em->getRepository(Student::class)->count([]);
        $guardiansCount = $em->getRepository(Guardian::class)->count([]);
        $attendanceCount = $em->getRepository(Attendance::class)->count([]);

        // 📈 نسبة الحضور
        $attendanceRate = 0;
        if ($studentsCount > 0) {
            $attendanceRate = round(($attendanceCount / $studentsCount) * 100, 2);
        }

        return $this->render('admin/dashboard.html.twig', [
            'studentsCount' => $studentsCount,
            'guardiansCount' => $guardiansCount,
            'attendanceCount' => $attendanceCount,
            'attendanceRate' => $attendanceRate,

            'chartData' => [
                'students' => $studentsCount,
                'guardians' => $guardiansCount,
                'attendance' => $attendanceCount,
            ]
        ]);
    }

    #[Route('/admin/add-student', name: 'admin_add_student')]
    public function addStudent(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(StudentWithGuardianType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $guardian = new Guardian();
            $guardian->setName($data['guardian_name']);
            $guardian->setPhone($data['guardian_phone']);

            $student = new Student();
            $student->setName($data['name']);
            $student->setLevel($data['level']);
            $student->setGuardian($guardian);

            $em->persist($guardian);
            $em->persist($student);
            $em->flush();

            $this->addFlash('success', 'تم إضافة الطالب وولي الأمر');

            return $this->redirectToRoute('admin_add_student');
        }

        return $this->render('admin/add_student.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/students', name: 'student_list')]
    public function studentsList(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $students = $em->getRepository(Student::class)->findAll();

        return $this->render('student/index.html.twig', [
            'students' => $students,
        ]);
    }
}
