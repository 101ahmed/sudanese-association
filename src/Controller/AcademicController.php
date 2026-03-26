<?php

namespace App\Controller;

use App\Entity\Attendance;
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
        // ✅ عدد الطلاب
        $studentsCount = $em->getRepository(Student::class)->count([]);

        // ✅ تاريخ اليوم
        $today = new \DateTime('today');

        // ✅ عدد الحضور الصحيح (بدون تكرار)
        $qb = $em->createQueryBuilder();

        $attendanceCount = $qb
            ->select('COUNT(DISTINCT a.student)')
            ->from(Attendance::class, 'a')
            ->where('a.date = :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();

        // ✅ آخر الطلاب
        $students = $em->getRepository(Student::class)
            ->findBy([], ['id' => 'DESC'], 5);

        return $this->render('academic/index.html.twig', [
            'studentsCount' => $studentsCount,
            'attendanceCount' => $attendanceCount,
            'students' => $students,
        ]);
    }
}
