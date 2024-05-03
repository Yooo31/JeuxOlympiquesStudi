<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class PaymentUserService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getPaymentCount(): int
    {
        return $this->entityManager->getRepository('App\Entity\Payment')->count([]);
    }

    public function getUserCount(): int
    {
        return $this->entityManager->getRepository('App\Entity\User')->count([]);
    }

    public function getVerifiedUserCount(): int
    {
        return $this->entityManager->getRepository('App\Entity\User')->count(['isVerified' => true]);
    }
}
