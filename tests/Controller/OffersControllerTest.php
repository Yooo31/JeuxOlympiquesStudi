<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OffersControllerTest extends WebTestCase
{
    public function testIndexPageDisplaysOffers()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/offers/');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorExists('#offers');

        $this->assertCount(3, $crawler->filter('#offerTitle'));

        $this->assertSelectorTextContains('#offers', 'Pack Solo');
        $this->assertSelectorTextContains('#offers', 'Pack Duo');
        $this->assertSelectorTextContains('#offers', 'Pack Famille');
    }
}
