<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControllerAdmin extends AbstractController
{
    #[Route('/admin/messages', name: 'admin_messages')]
    public function index(EntityManagerInterface $em): Response
    {
        $messages = $em->getRepository(ContactMessage::class)
            ->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/messages/index.html.twig', [
            'messages' => $messages
        ]);
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
