<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\News;
use App\Entity\Student;
use App\Entity\Guardian;
use App\Entity\Attendance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $em): Response
    {
        $studentsCount = $em->getRepository(Student::class)->count([]);
        $guardiansCount = $em->getRepository(Guardian::class)->count([]);
        $attendanceCount = $em->getRepository(Attendance::class)->count([]);
        $eventsCount = $em->getRepository(Event::class)->count([]);

        $newsRepo = $em->getRepository(News::class);

        // Gallery: صور من مجلد uploads (داخل subfolders أيضاً)
        $galleryImages = [];
        $uploadsDir = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (is_dir($uploadsDir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($uploadsDir, \FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $fileInfo) {
                if (!$fileInfo->isFile()) {
                    continue;
                }

                $ext = strtolower($fileInfo->getExtension());
                if (!in_array($ext, $allowedExt, true)) {
                    continue;
                }

                $relativePath = substr($fileInfo->getPathname(), strlen($uploadsDir) + 1);
                $relativePath = str_replace('\\', '/', $relativePath);

                $galleryImages[] = [
                    'path' => $relativePath,
                    'mtime' => $fileInfo->getMTime(),
                ];
            }

            usort($galleryImages, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
            $galleryImages = array_slice($galleryImages, 0, 8);
            $galleryImages = array_map(fn($i) => $i['path'], $galleryImages);
        }

        // Featured = أحدث خبر، وباقي الأخبار نستبعد نفس الخبر لتفادي التكرار
        $featured = $newsRepo->findOneBy([], ['id' => 'DESC']);

        if ($featured) {
            $news = $newsRepo
                ->createQueryBuilder('n')
                ->andWhere('n.id != :featuredId')
                ->setParameter('featuredId', $featured->getId())
                ->orderBy('n.id', 'DESC')
                ->setMaxResults(6)
                ->getQuery()
                ->getResult();
        } else {
            $news = $newsRepo->findBy([], ['id' => 'DESC'], 6);
        }

        return $this->render('home/index.html.twig', [
            'studentsCount' => $studentsCount,
            'guardiansCount' => $guardiansCount,
            'attendanceCount' => $attendanceCount,
            'eventsCount' => $eventsCount,
            'featured' => $featured,
            'news' => $news,
            'galleryImages' => $galleryImages,
        ]);
    }
}
