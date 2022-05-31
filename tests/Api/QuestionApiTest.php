<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\QuestionRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

class QuestionApiTest extends ApiTestCase
{
    const QUESTIONS_URL = '/api/questions';

    public function testGetAnswerList(): void
    {
        $client = static::createClient();

        // Нет токена
        $response = $client->request('GET', self::QUESTIONS_URL, [
            'query' => [
                'page' => 1
            ]
        ]);
        self::assertResponseStatusCodeSame(401);

        // Несуществующий токен
        $response = $client->request('GET', self::QUESTIONS_URL, [
            'headers' => [
                'X-AUTH-TOKEN' => 'some_hard_brootforce_token22222222222'
            ],
            'query' => [
                'page' => 1
            ]
        ]);
        self::assertResponseStatusCodeSame(401);

        // Все верно
        $response = $client->request('GET', self::QUESTIONS_URL, [
            'headers' => [
                'X-AUTH-TOKEN' => 'some_hard_brootforce_token2'
            ],
            'query' => [
                'page' => 1
            ]
        ]);
        self::assertResponseStatusCodeSame(200);

        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            "@context" => "/api/contexts/Question",
            "@id" => self::QUESTIONS_URL,
            "@type" => "hydra:Collection",
            'hydra:totalItems' => 4,
        ]);
        $this->assertCount(4, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Question::class);
    }

    public function testGetAnswer(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get('doctrine')->getManager();

        /** @var QuestionRepository $questionRepository */
        $questionRepository = $em->getRepository(Question::class);

        $response = $client->request('GET', self::QUESTIONS_URL, [
            'headers' => [
                'X-AUTH-TOKEN' => 'some_hard_brootforce_token2'
            ],
            'query' => [
                'page' => 1
            ]
        ]);

        $questionId = $response->toArray()['hydra:member'][0]['id'];
        $url = self::QUESTIONS_URL . '/' . $questionId;

        // Нет токена
        $response = $client->request('GET', $url);
        self::assertResponseStatusCodeSame(401);

        // Все верно
        $response = $client->request('GET', $url, [
            'headers' => [
                'X-AUTH-TOKEN' => 'some_hard_brootforce_token2'
            ],
        ]);
        self::assertResponseStatusCodeSame(200);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        /** @var Question $question */
        $question = $questionRepository->find($questionId);
        /** @var User $user */
        $user = $question->getAuthor();

        self::assertJsonContains([
            "@context" => "/api/contexts/Question",
            "@id" => self::QUESTIONS_URL . '/' . $questionId,
            "@type" => "Question",
            "id" => $questionId,
            "header" => $question->getHeader(),
            "text" => $question->getText(),
            "dateCreated" => $question->getDateCreated()->format(DateTimeInterface::W3C),
            "author" => [
                "@type" => "User",
                "id" => $user->getId(),
                "name" => $user->getName()
            ]
        ]);
        $this->assertMatchesResourceItemJsonSchema(Question::class);
    }
}
