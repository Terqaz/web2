<?php

namespace App\Tests\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use App\Tests\AbstractTest;

class QuestionTest extends AbstractTest
{
    public function testHomeAndDetailPages_notLogged(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertResponseStatusCodeSame(200);
        self::assertRouteSame('app_question_index');

        self::assertEquals(
            'Задать свой вопрос',
            $crawler->filter('.btn-primary h3')->first()->text()
        );
        // Кол-во вопросов
        self::assertEquals(3, $crawler->filter('.card')->count());

        $cardText1 = 'В чем отличие мьютекса от семафора?';

        $crawler = $client->clickLink($cardText1);
        self::assertRouteSame('app_question_show');
        self::assertResponseStatusCodeSame(200);
        self::assertEquals(
            $cardText1,
            $crawler->filter('h2.card-title')->first()->text()
        );
        // одна карточка с вопросом + кол-во ответов
        self::assertEquals(2, $crawler->filter('.card')->count());
        self::assertEquals(
            'Войдите или зарегистрируйтесь для возможности ответа на вопрос',
            $crawler->filter('.container-fluid h5')->first()->text()
        );
    }

    public function testLogin(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->request('GET', '/');
        $crawler = $client->clickLink('Вход');
        self::assertResponseStatusCodeSame(200);
        self::assertRouteSame('app_login');

        $form = $this->getFirstForm($crawler);
        // Проверка присутствия полей
        self::assertTrue(isset($form['email']));
        self::assertTrue(isset($form['password']));
        self::assertTrue(isset($form['_csrf_token']));

        // Неверный пароль
        $form['email'] = 'admin1@site.com';
        $form['password'] = '123456aaaaaaaaaaaaaaaaa';
        $crawler = $client->submit($form);
        self::assertEquals(
            'Invalid credentials.',
            $crawler->filter('div.alert-danger')->first()->text()
        );
        // Неверный логин
        $form['email'] = 'admin1@siteeeeeeeeee.com';
        $form['password'] = '123456';
        $crawler = $client->submit($form);
        self::assertEquals(
            'Invalid credentials.',
            $crawler->filter('div.alert-danger')->first()->text()
        );
        // Все верно
        $form['email'] = 'admin1@site.com';
        $form['password'] = '123456';
        $crawler = $client->submit($form);
        self::assertRouteSame('app_question_index');
    }

    public function testAddQuestion(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        /** @var QuestionRepository */
        $questionRepository = self::getEntityManager()->getRepository(Question::class);
        $moderatedCount = $questionRepository->count(['isModerated' => true]);
        $notModeratedCount = $questionRepository->count(['isModerated' => false]);

        $client->request('GET', '/');
        $crawler = $client->clickLink('Задать свой вопрос');
        self::assertResponseStatusCodeSame(200);
        self::assertRouteSame('app_login');

        $form = $this->getFirstForm($crawler);
        $form['email'] = 'user1@site.com';
        $form['password'] = '123456b';
        $crawler = $client->submit($form);
        self::assertRouteSame('app_question_new');

        $form = $this->getFirstForm($crawler);

        // Проверка присутствия полей
        self::assertTrue(isset($form['question[header]']));
        self::assertTrue(isset($form['question[text]']));
        self::assertTrue(isset($form['question[category]']));
        self::assertTrue(isset($form['question[_token]']));

        // Нет текста вопроса (неверно)
        $form['question[header]'] = '';
        $form['question[text]'] = 'Описание 1';
        $form['question[category]'] = 'Категория 1';
        $crawler = $client->submit($form);
        self::assertResponseStatusCodeSame(500);

        // Нет описания (верно)
        $form['question[header]'] = 'Вопрос 1';
        $form['question[text]'] = '';
        $form['question[category]'] = 'Категория 1';
        $crawler = $client->submit($form);
        self::assertResponseStatusCodeSame(200);

        // Нет категории (неверно)
        $form['question[header]'] = 'Вопрос 1';
        $form['question[text]'] = 'Описание 1';
        $form['question[category]'] = '';
        $crawler = $client->submit($form);
        self::assertResponseStatusCodeSame(500);

        // Есть все (верно)
        $form['question[header]'] = 'Вопрос 2';
        $form['question[text]'] = 'Описание 2';
        $form['question[category]'] = 'Категория 2';
        $crawler = $client->submit($form);
        self::assertResponseStatusCodeSame(200);

        // Добавленные вопросы непромодерированы
        self::assertEquals(
            $moderatedCount,
            $questionRepository->count(['isModerated' => true])
        );
        self::assertEquals(
            $notModeratedCount + 2,
            $questionRepository->count(['isModerated' => false])
        );

        $crawler = $client->request('GET', '/questions/');
        // Кол-во вопросов на главной не изменилось
        self::assertEquals(3, $crawler->filter('.card')->count());
    }
}
