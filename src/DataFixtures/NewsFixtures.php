<?php

namespace App\DataFixtures;

use App\Entity\News;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class NewsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {

            $news = new News();
            $news->setTitle('خبر رقم ' . $i);
            $news->setContent('هذا محتوى تجريبي للخبر رقم ' . $i);
            $news->setImage('https://via.placeholder.com/300');
            $news->setCreatedAt(new \DateTime());

            $manager->persist($news);
        }

        $manager->flush();
    }
}
