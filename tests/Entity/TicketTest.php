<?php

namespace App\Tests\Entity;

use App\Entity\Ticket;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TicketTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $ticket = new Ticket();

        $user = new User();

        $ticket->setUser($user);
        $this->assertSame($user, $ticket->getUser());

        $ticket->setConcatenedKey('test_concatened_key');
        $this->assertEquals('test_concatened_key', $ticket->getConcatenedKey());

        $ticket->setQrCode('test_qr_code');
        $this->assertEquals('test_qr_code', $ticket->getQrCode());
    }
}
