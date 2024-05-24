<?php

namespace App\Tests\Controller;

use App\Entity\Payment;
use App\Entity\Ticket;
use App\Repository\OffersRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends WebTestCase
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

        $client->request('GET', '/order/');
        $this->assertResponseRedirects('/login');
    }

    public function testAccessWithUserRoleNonVerified()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('user@nonverified.fr');

        $this->logIn($client, $testUser);

        $client->request('GET', '/order/');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAccessWithUserRoleVerified()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('user@verified.fr');

        $this->logIn($client, $testUser);

        $client->request('GET', '/order/');
        $this->assertResponseIsSuccessful();
    }

    public function testDiplayngOffersWithEmptyCartForOrder()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('user@verified.fr');

        $this->logIn($client, $testUser);

        $client->request('GET', '/order/');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('#emptyCart');
    }

    public function testDiplayngOffersWithOfferInCartForOrder()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('user@verified.fr');
        $this->logIn($client, $testUser);

        $offersRepository = static::getContainer()->get(OffersRepository::class);
        $offer = $offersRepository->findOneBy(['title' => 'Pack Solo']);
        $client->request('GET', '/cart/add/' . $offer->getId());
        $this->assertResponseRedirects('/cart/');
        $client->followRedirect();
        $this->assertSelectorTextContains('#flashSection', 'Produit ajouté au panier');
        $this->assertSelectorNotExists('#emptyCart');
        $this->assertSelectorExists('.cart-element');

        $client->request('GET', '/order/');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorNotExists('#emptyCart');
        $this->assertSelectorExists('.cart-element');
    }

    public function testPaymentProcessing()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('user@verified.fr');
        $this->logIn($client, $testUser);

        $client->request('GET', '/order/processing');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('p:contains("Transaction en cours")');
    }

    public function testPay()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $offersRepository = static::getContainer()->get(OffersRepository::class);
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $this->logIn($client, $testUser);

        // Add the same offer twice to the cart
        $offer = $offersRepository->findOneBy(['title' => 'Pack Solo']);
        $client->request('GET', '/cart/add/' . $offer->getId());
        $client->followRedirect();
        $client->request('GET', '/cart/add/' . $offer->getId());
        $client->followRedirect();

        // Prepare payment data
        $data = json_encode([$offer->getId() => 2]);
        $client->request('POST', '/order/pay', [
            'data' => $data,
            'userId' => $testUser->getId()
        ]);

        $this->assertResponseRedirects('/order/processing');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/order/success');
        $this->assertResponseIsSuccessful();

        // Check database for created payment
        $paymentRepository = $entityManager->getRepository(Payment::class);
        $payments = $paymentRepository->findBy(['user' => $testUser->getId(), 'offer' => $offer->getId()]);
        $this->assertCount(2, $payments);

        // Check database for created tickets
        $ticketRepository = $entityManager->getRepository(Ticket::class);
        $tickets = $ticketRepository->findBy(['user' => $testUser->getId()]);
        $this->assertCount(2, $tickets);

        // Clean up: remove created payments and tickets from the database
        foreach ($payments as $payment) {
            $entityManager->remove($payment);
        }
        foreach ($tickets as $ticket) {
            $entityManager->remove($ticket);
        }
        $entityManager->flush();
    }
}
