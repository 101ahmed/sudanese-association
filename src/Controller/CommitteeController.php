<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Entity\Event;
use App\Entity\Guardian;
use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommitteeController extends AbstractController
{
    #[Route('/committee/academic', name: 'committee_academic')]
    public function academic(EntityManagerInterface $em): Response
    {
        $guardians = $em->getRepository(Guardian::class)->findBy([], ['id' => 'DESC']);

        // نستخدم template موجود بالفعل لعرض guardians
        return $this->render('guardian/index.html.twig', [
            'guardians' => $guardians,
        ]);
    }

    #[Route('/committee/statistics', name: 'committee_statistics')]
    public function statistics(EntityManagerInterface $em): Response
    {
        $students = $em->getRepository(Student::class)->count([]);
        $attendance = $em->getRepository(Attendance::class)->count([]);

        return $this->render('statistics/index.html.twig', [
            'students' => $students,
            'attendance' => $attendance,
        ]);
    }

    #[Route('/committee/events', name: 'committee_events')]
    public function events(EntityManagerInterface $em): Response
    {
        $events = $em->getRepository(Event::class)->findBy([], ['id' => 'DESC'], 12);

        return $this->render('events/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/committee/media', name: 'committee_media')]
    public function media(): Response
    {
        return $this->render('media/index.html.twig', [
            'controller_name' => 'MediaController',
        ]);
    }

    #[Route('/committee/members', name: 'committee_members')]
    public function members(): Response
    {
        return $this->render('members/index.html.twig', [
            'controller_name' => 'MembersController',
        ]);
    }
}

