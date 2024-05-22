<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN'])
            ->setEmail('admin@admin.fr')
            ->setPassword($this->passwordHasher->hashPassword($user, 'admin'))
            ->setUsername('Admin')
            ->setVerified(true)
            ->setFirstName('Admin')
            ->setLastName('Admin')
            ->setPhone('0123456789')
            ->setAccountKey('c9187d98a0717ae19e2627d9d338b1b12ad241ae53df1ba23ba940a0acd23ad2');
        $manager->persist($user);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['production', 'test'];
    }
}
