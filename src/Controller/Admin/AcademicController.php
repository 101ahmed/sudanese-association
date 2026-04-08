<?php

namespace App\Controller\Admin;

use App\Entity\Attendance;
use App\Entity\Guardian;
use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AcademicController extends AbstractController
{
    #[Route('/admin/academic', name: 'academic_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $studentsCount = $em->getRepository(Student::class)->count([]);
        $guardiansCount = $em->getRepository(Guardian::class)->count([]);

        $today = new \DateTime('today');

        $attendanceCount = (int) $em->createQueryBuilder()
            ->select('COUNT(DISTINCT a.student)')
            ->from(Attendance::class, 'a')
            ->where('a.date = :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();

        $attendanceRate = $studentsCount > 0
            ? round(($attendanceCount / $studentsCount) * 100, 2)
            : 0;

        $students = $em->getRepository(Student::class)
            ->findBy([], ['id' => 'DESC'], 5);

        return $this->render('admin/academic/index.html.twig', [
            'studentsCount' => $studentsCount,
            'guardiansCount' => $guardiansCount,
            'attendanceCount' => $attendanceCount,
            'attendanceRate' => $attendanceRate,
            'students' => $students,

            // 🔥 الحل هنا
            'chartData' => [
                'students' => $studentsCount,
                'guardians' => $guardiansCount,
                'attendance' => $attendanceCount,
            ]
        ]);
    }
}
