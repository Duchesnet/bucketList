<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Wish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class WishFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = $manager->getRepository(Category::class)->findAll();
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 50; $i++) {
            $wish = new Wish();
            $wish
                ->setTitle($faker->sentence(2))
                ->setDescription($faker->paragraph())
                ->setAuthor($faker->name())
                ->setIsPublished($faker->boolean())
                ->setDateCreated($faker->dateTime())
                ->setCategory($faker->randomElement($categories));

            $manager->persist($wish);
        }

        $manager->flush();
    }
}
