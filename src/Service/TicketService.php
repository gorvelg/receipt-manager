<?php

namespace App\Service;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TicketService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function subtractionOfTicketsAmount(User $user, User $secondUser): float
    {
        $tickets1 = $this->em->getRepository(Ticket::class)->findBy([
            'user' => $user,
        ]);

        $tickets2 = $this->em->getRepository(Ticket::class)->findBy([
            'user' => $secondUser,
        ]);

        $totalAmount1 = 0;
        foreach ($tickets1 as $ticket) {
            $totalAmount1 += $ticket->getAmount();
        }

        $totalAmount2 = 0;
        foreach ($tickets2 as $ticket) {
            $totalAmount2 += $ticket->getAmount();
        }

        $totalAmount = $totalAmount1 - $totalAmount2;

        return $totalAmount;
    }
}
