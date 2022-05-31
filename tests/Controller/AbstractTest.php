<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

abstract class AbstractTest extends WebTestCase
{
    protected static function getFirstForm(Crawler $crawler): Form
    {
        return $crawler->filter('form')->first()->form();
    }

    protected static function getEntityManager()
    {
        return static::getContainer()->get('doctrine')->getManager();
    }
}