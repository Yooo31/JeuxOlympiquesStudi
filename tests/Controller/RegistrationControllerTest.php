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
        $form['registration_form[plainPassword]'] = 'password';

        $client->submit($form);

        $this->assertResponseRedirects('/');

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'john.doe@example.com']);
        $this->assertNotNull($user);

        // Vérifiez si l'utilisateur est connecté après l'inscription
        $this->assertTrue($client->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
    }
}
