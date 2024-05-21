<?php

namespace App\Tests\Entity;

use App\Entity\Offers;
use App\Entity\Payment;
use PHPUnit\Framework\TestCase;

class OffersTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $offer = new Offers();

        $offer->setTitle('Test Title');
        $this->assertEquals('Test Title', $offer->getTitle());

        $offer->setSubtitle('Test Subtitle');
        $this->assertEquals('Test Subtitle', $offer->getSubtitle());

        $offer->setPricing(100);
        $this->assertEquals(100, $offer->getPricing());

        $offer->setCapacity(50);
        $this->assertEquals(50, $offer->getCapacity());

        $createdAt = new \DateTimeImmutable();
        $offer->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $offer->getCreatedAt());

        $updatedAt = new \DateTimeImmutable();
        $offer->setUpdatedAt($updatedAt);
        $this->assertEquals($updatedAt, $offer->getUpdatedAt());

        $offer->setInactive(true);
        $this->assertTrue($offer->isInactive());
    }

    public function testAddAndRemovePayment()
    {
        $offer = new Offers();
        $payment = new Payment();

        $offer->addPayment($payment);
        $this->assertCount(1, $offer->getPayments());
        $this->assertTrue($offer->getPayments()->contains($payment));
        $this->assertSame($offer, $payment->getOffer());

        $offer->removePayment($payment);
        $this->assertCount(0, $offer->getPayments());
        $this->assertFalse($offer->getPayments()->contains($payment));
        $this->assertNull($payment->getOffer());
    }
}
