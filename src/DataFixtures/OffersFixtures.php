<?php

namespace App\DataFixtures;

use App\Entity\Offers;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OffersFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $offer = new Offers();
        $offer->setTitle('Pack Solo')
            ->setPricing(150)
            ->setCapacity(1)
            ->setInactive(0)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($offer);

        $offer = new Offers();
        $offer->setTitle('Pack Duo')
            ->setPricing(280)
            ->setCapacity(2)
            ->setInactive(0)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($offer);

        $offer = new Offers();
        $offer->setTitle('Pack Famille')
            ->setPricing(550)
            ->setCapacity(4)
            ->setInactive(0)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($offer);

        $manager->flush();
    }
}
