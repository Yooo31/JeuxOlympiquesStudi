<?php

namespace App\Tests\Controller;

use App\Entity\Offers;
use App\Repository\OffersRepository;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends WebTestCase
{
    private function logIn(KernelBrowser $client, UserInterface $user)
    {
        $session = $client->getContainer()->get('session.factory')->createSession();

        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    public function testAccessWithoutUserRoleRedirectsToLogin()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/');
        $this->assertResponseRedirects('/login');
    }

    public function testAccessWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('user@nonverified.fr');

        $this->logIn($client, $testUser);

        $client->request('GET', '/admin/');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAccessWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('admin@admin.fr');

        $this->logIn($client, $testUser);

        $client->request('GET', '/admin/');

        $this->assertResponseIsSuccessful();
    }

    public function testDecodeWithValidCode()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $paymentRepository = static::getContainer()->get(PaymentRepository::class);

        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $userCode = $userRepository->findOneByEmail('user@verified.fr');

        $this->logIn($client, $testUser);

        $testPayment = $paymentRepository->findOneBy(['user' => $userCode->getId()]);

        $validCode = $userCode->getAccountKey() . $testPayment->getPaymentKey();

        $client->request('POST', '/admin/decode', ['code' => $validCode]);

        $this->assertResponseIsSuccessful();

        $crawler = $client->getCrawler();

        $this->assertTrue($crawler->filter('#user-id')->count() > 0);

        $this->assertSelectorTextContains('#user-id', $userCode->getUsername());
        $this->assertSelectorTextContains('#offer-title', $testPayment->getOffer()->getTitle());
    }

    public function testDecodeWithInvalidCode()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('admin@admin.fr');

        $this->logIn($client, $testUser);

        $client->request('POST', '/admin/decode', ['code' => 'invalidcode']);

        $this->assertResponseIsSuccessful();

        $crawler = $client->getCrawler();

        $this->assertTrue($crawler->filter('#user-id')->count() == 0);
    }

    public function testShowStats()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $this->logIn($client, $testUser);

        $client->request('GET', '/admin/stats');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h2', 'Statistiques de ventes');
    }

    public function testShowOffers()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $this->logIn($client, $testUser);

        $client->request('GET', '/admin/offers');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('#offers');
        $inactiveStatusExists = $client->getCrawler()->filter('.status:contains("Inactif")')->count() == 1;
        $this->assertTrue($inactiveStatusExists, 'Au moins un élément avec le statut "Inactif" devrait être présent.');
    }

    public function testInactiveOffer()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $this->logIn($client, $testUser);

        $offerRepository = static::getContainer()->get(OffersRepository::class);
        $offer = $offerRepository->findOneBy(['title' => 'Pack Solo']);

        $client->request('GET', '/admin/offer/inactive/' . $offer->getId());
        $this->assertResponseRedirects('/admin/');

        $client->request('GET', '/admin/offers');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('#offers');
        $inactiveStatusExists = $client->getCrawler()->filter('.status:contains("Inactif")')->count() == 2;
        $this->assertTrue($inactiveStatusExists, 'Au moins un élément avec le statut "Inactif" devrait être présent.');
    }

    public function testActiveOffer()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $this->logIn($client, $testUser);

        $offerRepository = static::getContainer()->get(OffersRepository::class);
        $offer = $offerRepository->findOneBy(['title' => 'Pack Solo']);

        $client->request('GET', '/admin/offer/active/' . $offer->getId());
        $this->assertResponseRedirects('/admin/');

        $client->request('GET', '/admin/offers');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('#offers');
        $inactiveStatusExists = $client->getCrawler()->filter('.status:contains("Inactif")')->count() == 1;
        $this->assertTrue($inactiveStatusExists, 'Au moins un élément avec le statut "Inactif" devrait être présent.');
    }

    public function testOfferEdit()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $this->logIn($client, $testUser);

        $offerRepository = static::getContainer()->get(OffersRepository::class);
        $offer = $offerRepository->findOneBy(['title' => 'Pack Test']);

        $crawler = $client->request('GET', '/admin/offer/edit/' . $offer->getId());

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Confirmer')->form([
            'offers[title]' => 'Pack Test Edited',
            'offers[capacity]' => 10,
            'offers[pricing]' => 200,
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/admin/');

        $client->followRedirect();

        $this->assertSelectorTextContains('#flashSection', 'Offre modifiée avec succès');

        $offer = $client->getContainer()->get('doctrine')->getRepository(Offers::class)->findOneBy(['title' => 'Pack Test Edited']);
        $this->assertNotNull($offer);
        $this->assertEquals('Pack Test Edited', $offer->getTitle());
        $this->assertEquals(10, $offer->getCapacity());
        $this->assertEquals(200, $offer->getPricing());
        $this->assertFalse($offer->isInactive());
        $this->assertInstanceOf(\DateTimeImmutable::class, $offer->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $offer->getUpdatedAt());
    }

    public function testOfferEditBack()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $this->logIn($client, $testUser);

        $offerRepository = static::getContainer()->get(OffersRepository::class);
        $offer = $offerRepository->findOneBy(['title' => 'Pack Test Edited']);

        $crawler = $client->request('GET', '/admin/offer/edit/' . $offer->getId());

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Confirmer')->form([
            'offers[title]' => 'Pack Test',
            'offers[capacity]' => 10,
            'offers[pricing]' => 200,
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/admin/');

        $client->followRedirect();

        $this->assertSelectorTextContains('#flashSection', 'Offre modifiée avec succès');

        $offer = $client->getContainer()->get('doctrine')->getRepository(Offers::class)->findOneBy(['title' => 'Pack Test']);
        $this->assertNotNull($offer);
        $this->assertEquals('Pack Test', $offer->getTitle());
        $this->assertEquals(10, $offer->getCapacity());
        $this->assertEquals(200, $offer->getPricing());
        $this->assertFalse($offer->isInactive());
        $this->assertInstanceOf(\DateTimeImmutable::class, $offer->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $offer->getUpdatedAt());
    }

    public function testOfferCreate()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $this->logIn($client, $testUser);

        $crawler = $client->request('GET', '/admin/offer/create');

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Confirmer')->form([
            'offers[title]' => 'Pack Test Create',
            'offers[capacity]' => 10,
            'offers[pricing]' => 200,
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/admin/');

        $client->followRedirect();

        $this->assertSelectorTextContains('#flashSection', 'Offre ajouté avec succès');

        $offer = $client->getContainer()->get('doctrine')->getRepository(Offers::class)->findOneBy(['title' => 'Pack Test Create']);
        $this->assertNotNull($offer);
        $this->assertEquals('Pack Test Create', $offer->getTitle());
        $this->assertEquals(10, $offer->getCapacity());
        $this->assertEquals(200, $offer->getPricing());
        $this->assertFalse($offer->isInactive());
        $this->assertInstanceOf(\DateTimeImmutable::class, $offer->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $offer->getUpdatedAt());

        $em = $client->getContainer()->get('doctrine')->getManager();
        $em->remove($offer);
        $em->flush();

        $offer = $client->getContainer()->get('doctrine')->getRepository(Offers::class)->findOneBy(['title' => 'Pack Test Create']);
        $this->assertNull($offer);
    }
}