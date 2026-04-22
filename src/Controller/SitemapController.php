<?php

namespace App\Controller;

use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'sitemap', methods: ['GET'])]
    public function __invoke(
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
    ): Response {
        $staticRoutes = [
            'home',
            'about',
            'contact',
            'events_list',
            'public_news_list',
            'search',
            'committee_academic',
            'committee_statistics',
            'committee_events',
            'committee_media',
            'committee_members',
        ];

        $urls = [];
        foreach ($staticRoutes as $name) {
            try {
                $urls[] = [
                    'loc' => $urlGenerator->generate($name, [], UrlGeneratorInterface::ABSOLUTE_URL),
                    'changefreq' => 'weekly',
                    'priority' => $name === 'home' ? '1.0' : '0.8',
                ];
            } catch (\Throwable) {
            }
        }

        $newsItems = $em->getRepository(News::class)->findBy([], ['id' => 'DESC'], 500);
        foreach ($newsItems as $news) {
            $urls[] = [
                'loc' => $urlGenerator->generate('public_news_show', ['id' => $news->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        $parts = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
        foreach ($urls as $u) {
            $parts[] = '<url>'
                . '<loc>' . htmlspecialchars($u['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>'
                . '<changefreq>' . htmlspecialchars($u['changefreq'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</changefreq>'
                . '<priority>' . htmlspecialchars($u['priority'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</priority>'
                . '</url>';
        }
        $parts[] = '</urlset>';

        return new Response(implode('', $parts), Response::HTTP_OK, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
