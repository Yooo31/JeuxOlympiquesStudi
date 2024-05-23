<?php

namespace App\Tests\Controller;

use App\Repository\OffersRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class CartControllerTest extends WebTestCase
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

        $client->request('GET', '/cart/');
        $this->assertResponseRedirects('/login');
    }

    public function testAccessWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('user@verified.fr');

        $this->logIn($client, $testUser);

        $client->request('GET', '/cart/');
        $this->assertResponseIsSuccessful();
    }

    public function testAddToCart()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $offersRepository = static::getContainer()->get(OffersRepository::class);

        $testUser = $userRepository->findOneByEmail('user@verified.fr');
        $offer = $offersRepository->findOneBy(['title' => 'Pack Solo']);

        $this->logIn($client, $testUser);

        $client->request('GET', '/cart/add/' . $offer->getId());
        $this->assertResponseRedirects('/cart/');

        $client->followRedirect();
        $this->assertSelectorTextContains('#flashSection', 'Produit ajouté au panier');
        $this->assertSelectorNotExists('#emptyCart');
        $this->assertSelectorExists('.cart-element');
    }

    public function testRemoveOneFromCart()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $offersRepository = static::getContainer()->get(OffersRepository::class);

        $testUser = $userRepository->findOneByEmail('user@verified.fr');
        $offer = $offersRepository->findOneBy(['title' => 'Pack Solo']);

        $this->logIn($client, $testUser);

        $client->request('GET', '/cart/add/' . $offer->getId());
        $this->assertResponseRedirects('/cart/');

        $client->followRedirect();
        $this->assertSelectorTextContains('#flashSection', 'Produit ajouté au panier');
        $this->assertSelectorNotExists('#emptyCart');
        $this->assertSelectorExists('.cart-element');

        $client->request('GET', '/cart/remove/' . $offer->getId());
        $this->assertResponseRedirects('/cart/');

        $client->followRedirect();
        $this->assertSelectorTextContains('#flashSection', 'Un produit a été retiré du panier');
        $this->assertSelectorNotExists('.cart-element');
        $this->assertSelectorExists('#emptyCart');
    }

    public function testRemoveCart()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $offersRepository = static::getContainer()->get(OffersRepository::class);

        $testUser = $userRepository->findOneByEmail('user@verified.fr');
        $offer = $offersRepository->findOneBy(['title' => 'Pack Solo']);

        $this->logIn($client, $testUser);

        $client->request('GET', '/cart/add/' . $offer->getId());
        $this->assertResponseRedirects('/cart/');

        $client->followRedirect();
        $this->assertSelectorTextContains('#flashSection', 'Produit ajouté au panier');

        $this->assertSelectorNotExists('#emptyCart');
        $this->assertSelectorExists('.cart-element');

        $client->request('GET', '/cart/add/' . $offer->getId());
        $this->assertResponseRedirects('/cart/');

        $client->followRedirect();
        $this->assertSelectorTextContains('#flashSection', 'Produit ajouté au panier');

        $this->assertSelectorNotExists('#emptyCart');
        $this->assertSelectorExists('.cart-element');

        $client->request('GET', '/cart/delete/' . $offer->getId());
        $this->assertResponseRedirects('/cart/');

        $client->followRedirect();
        $this->assertSelectorTextContains('#flashSection', 'Le produit a été retiré du panier');
        $this->assertSelectorNotExists('.cart-element');
        $this->assertSelectorExists('#emptyCart');
    }
}