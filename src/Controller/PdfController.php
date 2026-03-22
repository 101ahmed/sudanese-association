<?php

namespace App\Controller;

use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PdfController extends AbstractController
{
    #[Route('/admin/send/students', name: 'send_students_pdf')]
    public function sendStudentsPdf(EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $students = $em->getRepository(Student::class)->findAll();

        // 🧾 HTML → PDF
        $html = $this->renderView('pdf/students.html.twig', [
            'students' => $students
        ]);

        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->render();

        $output = $pdf->output();

        // 📧 Email
        $email = (new Email())
            ->from('admin@test.com')
            ->to('test@test.com') // ✏️ ضع بريدك هنا
            ->subject('📄 تقرير الطلاب')
            ->text('مرفق تقرير الطلاب')
            ->attach($output, 'students.pdf', 'application/pdf');

        $mailer->send($email);

        return new Response('✅ تم إرسال التقرير بالبريد');
    }
}
