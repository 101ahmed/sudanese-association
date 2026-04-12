<?php

namespace App\Controller\Admin;

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

            // ✅ تأكد من اختيار الطالب
            if (!$attendance->getStudent()) {
                $this->addFlash('error', 'يجب اختيار الطالب');
                return $this->redirectToRoute('attendance_create');
            }

            // ✅ حدود اليوم لاستخدام مقارنة تاريخ موثوقة
            $todayStart = new \DateTimeImmutable('today');
            $tomorrowStart = $todayStart->modify('+1 day');

            // ✅ تحقق من عدم التكرار
            $existing = $em->getRepository(Attendance::class)
                ->createQueryBuilder('a')
                ->andWhere('a.student = :student')
                ->andWhere('a.date >= :todayStart')
                ->andWhere('a.date < :tomorrowStart')
                ->setParameter('student', $attendance->getStudent())
                ->setParameter('todayStart', $todayStart)
                ->setParameter('tomorrowStart', $tomorrowStart)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($existing) {
                $this->addFlash('error', 'هذا الطالب مسجل حضور بالفعل اليوم');
                return $this->redirectToRoute('attendance_create');
            }

            // ✅ تعيين التاريخ
            $attendance->setDate($todayStart);

            // ✅ حفظ
            $em->persist($attendance);
            $em->flush();

            $this->addFlash('success', 'تم تسجيل الحضور بنجاح');

            return $this->redirectToRoute('attendance_create');
        }

        return $this->render('attendance/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
