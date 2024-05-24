<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegister()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('S\'enregistrer')->form();

        $form['registration_form[first_name]'] = 'John';
        $form['registration_form[last_name]'] = 'Doe';
        $form['registration_form[email]'] = 'john.doe@example.com';
        $form['registration_form[phone]'] = '0101010101';
        $form['registration_form[plainPassword]'] = 'Password31.';
        $form['registration_form[agreeTerms]']->tick();


        $client->submit($form);

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'john.doe@example.com']);
        $this->assertNotNull($user);

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
    }

    public function testRegisterError()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('S\'enregistrer')->form();

        $form['registration_form[first_name]'] = 'John';
        $form['registration_form[last_name]'] = 'Doe';
        $form['registration_form[email]'] = 'john.doe@example.com';
        $form['registration_form[phone]'] = '0101010101';
        $form['registration_form[plainPassword]'] = 'test';
        $form['registration_form[agreeTerms]']->tick();


        $client->submit($form);

        $this->assertEquals(422, $client->getResponse()->getStatusCode());

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'john.doe@example.com']);
        $this->assertNull($user);
    }
}
