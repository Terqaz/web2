<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Screenshot;
use App\Entity\User;
use \DateTime;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i=0; $i < 15; $i++) { 
            $screenshot = new Screenshot();
            $screenshot
                ->setCreationDate($this->generateDate())
                ->setIsPrivate(boolval(rand(0, 1)))
                ->setUrl("user/img/" . uniqid() . ".png");
            $manager->persist($screenshot);
        }

        for ($i=0; $i < 15; $i++) { 
            $user = new User();
            $user
                ->setName('Крутой никнейм'.$i)
                ->setEmail('cool.email'.$i.'@gmail.com')
                ->setPhone(rand(1_000_000_000, 9_999_999_999))
                ->setPasswordHash(password_hash($i, PASSWORD_DEFAULT));
            $manager->persist($user);
        }

        $manager->flush();
    }

    private function generateDate() : DateTime
    {
        $randDate = rand(2020, 2022) . '-' . rand(1, 12) . '-' . rand(1, 29) . ' ' .
            rand(10, 23) . ':' . rand(10, 59) . ':' . rand(10, 59);
        return DateTime::createFromFormat('Y-m-d H:i:s', $randDate);
    }
}
