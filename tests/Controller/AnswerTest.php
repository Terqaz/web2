<?php

namespace App\Tests\Controller;

use App\Entity\Answer;
use App\Repository\AnswerRepository;
use App\Tests\AbstractTest;

class AnswerTest extends AbstractTest
{
    public function testAddAnswer(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        /** @var AnswerRepository */
        $answerRepository = self::getEntityManager()->getRepository(Answer::class);
        $moderatedCount = $answerRepository->count(['isModerated' => true]);
        $notModeratedCount = $answerRepository->count(['isModerated' => false]);

        $client->request('GET', '/');

        $crawler = $client->clickLink('Вход');
        $form = $this->getFirstForm($crawler);
        $form['email'] = 'user1@site.com';
        $form['password'] = '123456b';
        $crawler = $client->submit($form);

        $crawler = $client->clickLink('В чем отличие мьютекса от семафора?');
        $form = $this->getFirstForm($crawler);

        // Проверка присутствия полей
        self::assertTrue(isset($form['text']));
        self::assertTrue(isset($form['questionId']));
        self::assertTrue(isset($form['_token']));

        // Нет текста ответа (неверно)
        $form['text'] = '';
        $crawler = $client->submit($form);
        self::assertResponseStatusCodeSame(500);

        // Есть все (верно)
        $form['text'] = 'Ответ 1';
        $crawler = $client->submit($form);
        self::assertResponseStatusCodeSame(200);
        self::assertRouteSame('app_question_show');

        // Добавленные вопросы непромодерированы
        self::assertEquals(
            $moderatedCount,
            $answerRepository->count(['isModerated' => true])
        );
        self::assertEquals(
            $notModeratedCount + 1,
            $answerRepository->count(['isModerated' => false])
        );

        // одна карточка с вопросом + кол-во ответов
        self::assertEquals(2, $crawler->filter('.card')->count());
    }
}
