<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Answer;
use App\Entity\User;
use App\Entity\Question;

class AppFixtures extends Fixture
{
    private const USERS = [
        [
            'name' => 'Великий прогер'
        ], [
            'name' => 'Кирилл Максимов'
        ], [
            'name' => 'Иванов Иван'
        ], [
            'name' => 'Micah Jordan'
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
            'text' => "а) крахмал;\nб) фруктоза;\nв) мальтоза;\nг) целлюлоза.",
            'category' => 'LUA',
            'isModerated' => true
        ], [
            'header' => '6. Установите последовательность углеводов по мере увеличения молекулярной массы',
            'text' => '',
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

    public function load(ObjectManager $manager): void
    {
        $users = [];

        foreach (self::USERS as $i => $user) {
            $user = new User($user['name']);

            $manager->persist($user);
            $users[] = $user;
        }

        foreach (self::QUESTIONS as $i => $question) {
            $question = (new Question())
                ->setAuthor($this->randomElement($users))
                ->setHeader($question['header'])
                ->setText($question['text'])
                ->setCategory($question['category'])
                ->setIsModerated($question['isModerated']);

            $manager->persist($question);

            foreach (self::ANSWERS[$i] as $j => $answer) {
                $answer = (new Answer())
                    ->setAuthor($this->randomElement($users))
                    ->setQuestion($question)
                    ->setText($answer['text'])
                    ->setIsModerated($answer['isModerated']);

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
