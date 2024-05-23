<?php

namespace App\DataFixtures;

use App\Entity\Offers;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Groups({"test"})
 */
class TestFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadOfferData($manager);
        $this->loadUserData($manager);
        $manager->flush();

        $this->loadPaymentData($manager);
        $manager->flush();
    }

    private function loadOfferData(ObjectManager $manager)
    {
        $offer = new Offers();
        $offer->setTitle('Pack Test')
            ->setPricing(200)
            ->setCapacity(10)
            ->setInactive(0)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($offer);

        $offer = new Offers();
        $offer->setTitle('Pack Test Inactive')
            ->setPricing(200)
            ->setCapacity(10)
            ->setInactive(1)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($offer);
    }

    public function loadUserData(ObjectManager $manager): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER'])
            ->setEmail('user@verified.fr')
            ->setPassword($this->passwordHasher->hashPassword($user, 'user'))
            ->setUsername('UserVerified')
            ->setVerified(true)
            ->setFirstName('User')
            ->setLastName('User')
            ->setPhone('0000000000')
            ->setAccountKey('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
        $manager->persist($user);

        $user = new User();
        $user->setRoles(['ROLE_USER'])
            ->setEmail('user@nonverified.fr')
            ->setPassword($this->passwordHasher->hashPassword($user, 'user'))
            ->setUsername('User')
            ->setVerified(false)
            ->setFirstName('User')
            ->setLastName('User')
            ->setPhone('0000000000')
            ->setAccountKey('c9187d98a0717ae19e2627d9d338b1b12ad241ae53df1ba23ba940a0acd23c53');
        $manager->persist($user);
    }

    public function loadPaymentData(ObjectManager $manager): void
    {
        // Récupérer l'utilisateur UserVerified
        $userVerified = $manager->getRepository(User::class)->findOneByEmail('user@verified.fr');

        // Récupérer le pack Pack Test
        $packTest = $manager->getRepository(Offers::class)->findOneBy(['title' => 'Pack Test']);

        // Vérifier si les entités ont été trouvées
        if (!$userVerified || !$packTest) {
            throw new \Exception('UserVerified or Pack Test not found.');
        }

        // Créer le paiement et le ticket
        $payment = new Payment();
        $payment->setUser($userVerified) // Associer l'utilisateur UserVerified
            ->setOffer($packTest) // Associer le pack Pack Test
            ->setPaymentKey('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb')
            ->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($payment);

        $ticket = new Ticket();
        $ticket->setUser($userVerified) // Associer l'utilisateur UserVerified
            ->setConcatenedKey('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaabbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb')
            ->setQrCode('qr_code_test');
        $manager->persist($ticket);

        $manager->flush();
    }



    public static function getGroups(): array
    {
        return ['test'];
    }
}
