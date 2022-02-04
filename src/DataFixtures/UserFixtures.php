<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Migrations\Exception\DependencyException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{

    private \Faker\Generator $faker;

    private ObjectManager $manager;

    private UserPasswordEncoderInterface $passwordEncoder; 

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        
        $this->faker = Factory::create();

        $this->generateUsers(2);

        $this->manager->flush();
    }

    public function getDependencies()
    {
        return [
            AuthorFixtures::class,
        ];
    }

    private function generateUsers(int $number): void
    {

        $isVerified = [true, false];

        for ($i = 0; $i < $number; $i ++) {
            $user = new User();

            $user->setEmail($this->faker->email())
                 ->setPassword($this->passwordEncoder->encodePassword($user, 'badpassword'))
                 ->setIsVerified($isVerified[$i])
                 ->setAuthor($this->getReference("author{$i}"));

            $this->manager->persist($user);
        }
    }
}
