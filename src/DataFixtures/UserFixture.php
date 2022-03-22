<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i=0; $i < 15; $i++) { 
            $user = new User();
            $user
                ->setName('Крутой никнейм '.$i)
                ->setEmail('cool.email'.$i.'@gmail.com')
                ->setPhone('8005553535')
                ->setPasswordHash(password_hash($i, PASSWORD_DEFAULT));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
