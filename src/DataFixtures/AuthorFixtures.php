<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Author;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AuthorFixtures extends Fixture
{

    private \Faker\Generator $faker;

    private ObjectManager $manager;

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        $this->faker = Factory::create();

        $this->generateAuthors(2);

        $this->manager->flush();
    }

    private function generateAuthors(int $number): void 
    {
        for ($i = 0; $i < $number; $i++) {

            $author = (new Author())->setName($this->faker->name());
            // $author = (new Author())->setName("Test");

            $this->addReference("author{$i}", $author);

            $this->manager->persist($author);
        }
    }
}
