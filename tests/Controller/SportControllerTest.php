<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SportControllerTest extends WebTestCase
{
    public function testIndexPageDisplaysSports()
    {
        $client = static::createClient();
        $client->request('GET', '/sports/');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}