<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Answer;
use App\Entity\User;
use App\Repository\AnswerRepository;
use App\Tests\TestUtils;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

class AnswerApiTest extends ApiTestCase
{
    const ANSWERS_URL = '/api/answers';

    public function testGetAnswerList(): void
    {
        $client = static::createClient();

        // Нет токена
        $response = $client->request('GET', self::ANSWERS_URL, [
            'query' => [
                'page' => 1
            ]
        ]);
        self::assertResponseStatusCodeSame(401);

        // Несуществующий токен
        $response = $client->request('GET', self::ANSWERS_URL, [
            'headers' => [
                'X-AUTH-TOKEN' => 'some_hard_brootforce_token22222222222'
            ],
            'query' => [
                'page' => 1
            ]
        ]);
        self::assertResponseStatusCodeSame(401);

        // Все верно
        $response = $client->request('GET', self::ANSWERS_URL, [
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
            "@context" => "/api/contexts/Answer",
            "@id" => self::ANSWERS_URL,
            "@type" => "hydra:Collection",
            'hydra:totalItems' => 4,
        ]);
        $this->assertCount(4, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Answer::class);
    }

    public function testGetAnswer(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get('doctrine')->getManager();

        /** @var AnswerRepository $answerRepository */
        $answerRepository = $em->getRepository(Answer::class);

        $response = $client->request('GET', self::ANSWERS_URL, [
            'headers' => [
                'X-AUTH-TOKEN' => 'some_hard_brootforce_token2'
            ],
            'query' => [
                'page' => 1
            ]
        ]);

        $answerId = $response->toArray()['hydra:member'][0]['id'];
        $url = self::ANSWERS_URL . '/' . $answerId;

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

        /** @var Answer $answer */
        $answer = $answerRepository->find($answerId);
        /** @var User $user */
        $user = $answer->getAuthor();

        self::assertJsonContains([
            "@context" => "/api/contexts/Answer",
            "@id" => self::ANSWERS_URL . '/' . $answerId,
            "@type" => "Answer",
            "id" => $answerId,
            "text" => $answer->getText(),
            "dateCreated" => $answer->getDateCreated()->format(DateTimeInterface::W3C),
            "author" => [
                "@type" => "User",
                "id" => $user->getId(),
                "name" => $user->getName()
            ]
        ]);
        $this->assertMatchesResourceItemJsonSchema(Answer::class);
    }
}
