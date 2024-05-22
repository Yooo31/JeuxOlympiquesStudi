<?php

namespace App\DataFixtures;

use App\Entity\Offers;
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
        $offer->setTitle('Pack Test')
            ->setPricing(200)
            ->setCapacity(10)
            ->setInactive(1)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($offer);
    }


    public static function getGroups(): array
    {
        return ['test'];
    }
}
