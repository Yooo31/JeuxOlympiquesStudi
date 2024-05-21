<?php

namespace App\Tests\Entity;

use App\Entity\Payment;
use App\Entity\Offers;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $payment = new Payment();

        $user = new User();
        $offer = new Offers();

        $payment->setUser($user);
        $this->assertSame($user, $payment->getUser());

        $payment->setOffer($offer);
        $this->assertSame($offer, $payment->getOffer());

        $payment->setPaymentKey('test_payment_key');
        $this->assertEquals('test_payment_key', $payment->getPaymentKey());

        $createdAt = new \DateTimeImmutable();
        $payment->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $payment->getCreatedAt());
    }
}
