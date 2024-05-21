<?php

namespace App\Tests\Entity;

use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $user = new User();

        $user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $user->getEmail());

        $user->setPassword('test_password');
        $this->assertEquals('test_password', $user->getPassword());

        $user->setUsername('test_username');
        $this->assertEquals('test_username', $user->getUsername());

        $user->setVerified(true);
        $this->assertTrue($user->isVerified());

        $user->setFirstName('John');
        $this->assertEquals('John', $user->getFirstName());

        $user->setLastName('Doe');
        $this->assertEquals('Doe', $user->getLastName());

        $user->setPhone(123456789);
        $this->assertEquals(123456789, $user->getPhone());

        $user->setAccountKey('test_account_key');
        $this->assertEquals('test_account_key', $user->getAccountKey());
    }

    public function testRoles()
    {
        $user = new User();

        $this->assertEquals(['ROLE_USER'], $user->getRoles());

        $user->setRoles(['ROLE_ADMIN']);
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());

        $user->setVerified(true);
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER', 'ROLE_VERIFIED'], $user->getRoles());
    }

    public function testAddAndRemovePayment()
    {
        $user = new User();
        $payment = new Payment();

        $user->addPayment($payment);
        $this->assertCount(1, $user->getPayments());
        $this->assertTrue($user->getPayments()->contains($payment));
        $this->assertSame($user, $payment->getUser());

        $user->removePayment($payment);
        $this->assertCount(0, $user->getPayments());
        $this->assertFalse($user->getPayments()->contains($payment));
        $this->assertNull($payment->getUser());
    }

    public function testAddAndRemoveTicket()
    {
        $user = new User();
        $ticket = new Ticket();

        $user->addTicket($ticket);
        $this->assertCount(1, $user->getTickets());
        $this->assertTrue($user->getTickets()->contains($ticket));
        $this->assertSame($user, $ticket->getUser());

        $user->removeTicket($ticket);
        $this->assertCount(0, $user->getTickets());
        $this->assertFalse($user->getTickets()->contains($ticket));
        $this->assertNull($ticket->getUser());
    }
}
