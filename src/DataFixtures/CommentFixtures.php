<?php

namespace App\DataFixtures;

use faker;
use App\Entity\Comment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        for($i=0; $i<30; $i++) {
            $comment = new Comment();
            $comment->setContent($faker->paragraph(rand(1, 3)))
                ->setUser(user: $this->getReference('user' . rand (0, 4)))
                ->setPost($this->getReference('post' . rand(0, 9)))
                ->setCreatedAt($faker->dateTimeBetween('-7days'));

            $manager->persist($comment);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            PostFixtures::class,
        ];
    }

}
