<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class AccountControllerTest extends WebTestCase
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

        $client->request('GET', '/account/');
        $this->assertResponseRedirects('/login');
    }

    public function testAccessWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('user@nonverified.fr');

        $this->logIn($client, $testUser);

        $client->request('GET', '/account/');

        $this->assertResponseIsSuccessful();
    }

    public function testSendVerificationEmailAlreadyVerified()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneBy(['email' => 'user@verified.fr']);
        $testUser->setVerified(true);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->flush();

        $this->logIn($client, $testUser);

        $client->request('GET', '/account/send-verification');

        $this->assertResponseRedirects('/account/');
    }

    public function testSendVerificationEmailNotVerified()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneBy(['email' => 'user@nonverified.fr']);
        $testUser->setVerified(false);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->flush();

        $this->logIn($client, $testUser);

        $client->request('GET', '/account/send-verification');

        $this->assertResponseRedirects('/account/');

        $updatedUser = $userRepository->findOneBy(['email' => 'user@nonverified.fr']);
        $this->assertTrue($updatedUser->isVerified());
    }

    public function testAccessTicketList()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('user@verified.fr');
        $this->logIn($client, $testUser);

        $client->request('GET', '/account/tickets');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('#ticket-list');
    }
}
