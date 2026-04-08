<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {

            $name = $request->request->get('name');
            $emailUser = $request->request->get('email');
            $subject = $request->request->get('subject');
            $content = $request->request->get('message');

            // 💾 حفظ في DB
            $message = new ContactMessage();
            $message->setName($name);
            $message->setEmail($emailUser);
            $message->setSubject($subject);
            $message->setMessage($content);
            $message->setCreatedAt(new \DateTimeImmutable());
            $message->setIsRead(false);

            $em->persist($message);
            $em->flush();

            // 📧 إرسال الإيميل
            $email = (new Email())
                ->from('abdelahmed.alsare@gmail.com')
                ->to('abdelahmed.alsare@gmail.com')
                ->replyTo($emailUser)
                ->subject($subject ?: 'رسالة جديدة')
                ->text(
                    "الاسم: $name\n".
                    "الإيميل: $emailUser\n\n".
                    "الرسالة:\n$content"
                );

            $mailer->send($email);

            $this->addFlash('success', '✅ تم إرسال الرسالة');

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig');
    }

    #[Route('/admin/messages/read/{id}', name: 'admin_message_read')]
    public function markAsRead(ContactMessage $message, EntityManagerInterface $em): Response
    {
        $message->setIsRead(true);
        $em->flush();

        return $this->redirectToRoute('admin_messages');
    }

    #[Route('/admin/messages/delete/{id}', name: 'admin_message_delete')]
    public function delete(ContactMessage $message, EntityManagerInterface $em): Response
    {
        $em->remove($message);
        $em->flush();

        return $this->redirectToRoute('admin_messages');
    }

}

