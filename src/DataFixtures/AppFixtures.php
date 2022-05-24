<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const USERS = [
        [
            'name' => 'Великий прогер',
            'roles' => ['ROLE_ADMIN'],
            'email' => 'admin1@site.com',
            'password' => '123456',
            'apiToken' => 'some_hard_brootforce_token1'

        ], [
            'name' => 'Кирилл Максимов',
            'roles' => ['ROLE_ADMIN'],
            'email' => 'admin2@site.com',
            'password' => '123456a',

        ], [
            'name' => 'Иванов Иван',
            'roles' => [],
            'email' => 'user1@site.com',
            'password' => '123456b',
            'apiToken' => 'some_hard_brootforce_token2'
        ], [
            'name' => 'Micah Jordan',
            'roles' => [],
            'email' => 'user2@site.com',
            'password' => '123456c',
        ]
    ];

    private const QUESTIONS = [
        [
            'header' => 'ПОМОГИТЕ С++ СРОЧНО!!!!!!!!!!!!!!!!!!!!!!!!!!',
            'text' => '12.Создать двоичный файл, куда записать n целых чисел. Из файла переписать все простые большие среднего арифметические в новый файл. С++',
            'category' => 'Информатика',
            'isModerated' => false
        ], [
            'header' => 'В чем отличие мьютекса от семафора?',
            'text' => '',
            'category' => 'Другие языки и технологии',
            'isModerated' => true
        ], [
            'header' => 'Если в LUA поставить запятую после последней переменной то будет ли это считатся трудно уловимой ошибкой?',
            'text' => '',
            'category' => 'LUA',
            'isModerated' => true
        ], [
            'header' => '6. Установите последовательность углеводов по мере увеличения молекулярной массы',
            'text' => "а) крахмал;\nб) фруктоза;\nв) мальтоза;\nг) целлюлоза.",
            'category' => 'Химия',
            'isModerated' => true
        ]
    ];

    private const ANSWERS = [
        [],
        [
            [
                'text' => 'У них, в общем-то, одинаковое предназначение: синхронизировать доступ к какому-то ресурсу',
                'isModerated' => true
            ]
        ], [
            [
                'text' => 'Нет, такая запись вполне допустима и не влияет на работоспособность кода.',
                'isModerated' => true
            ],[
                'text' => 'Если оставить запятую в коде, то это будет сразу ошибкой, но в массиве или таблице это считается нормой, ведь в учёт идут только значения.',
                'isModerated' => false
            ]
        ], [
            [
                'text' => 'б), в), а), г).',
                'isModerated' => false
            ]
        ]
    ];

    private $passwordHasher;

    public function __construct (UserPasswordHasherInterface $passwordHasher) 
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [];

        foreach (self::USERS as $i => $userData) {
            $user = (new User())
                ->setName($userData['name'])
                ->setRoles($userData['roles'])
                ->setEmail($userData['email']);
            if (isset($userData['apiToken'])) {
                $user->setApiToken($userData['apiToken']);
            }
            $user->setPassword(
                    $this->passwordHasher->hashPassword(
                        $user,
                        $userData['password']
                    )
                );
            $manager->persist($user);
            $users[] = $user;
        }

        foreach (self::QUESTIONS as $i => $question) {
            $questionTimestamp = 1652868287 - rand(10**6, 10**7);

            $question = (new Question())
                ->setAuthor($this->randomElement($users))
                ->setHeader($question['header'])
                ->setText($question['text'])
                ->setCategory($question['category'])
                ->setIsModerated($question['isModerated'])
                ->setDateCreated(
                    (new DateTime())->setTimestamp($questionTimestamp)
                );

            $manager->persist($question);

            foreach (self::ANSWERS[$i] as $j => $answer) {
                $answer = (new Answer())
                    ->setAuthor($this->randomElement($users))
                    ->setQuestion($question)
                    ->setText($answer['text'])
                    ->setIsModerated($answer['isModerated'])
                    ->setDateCreated(
                        (new DateTime())->setTimestamp($questionTimestamp + rand(10**4, 5 * 10**5))
                    );

                $manager->persist($answer);
            }
        }
        $manager->flush();
    }

    private function randomElement($arr)
    {
        return $arr[array_rand($arr)];
    }
}
