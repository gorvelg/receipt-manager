<?php

namespace App\Service;

use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;

class TicketService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function subtractionOfTicketsAmount(int $userA, int $userB): string
    {
        $amountA = $this->calculateTotalAmount($this->getUserTickets($userA));
        $amountB = $this->calculateTotalAmount($this->getUserTickets($userB));

        $subtractionResult = ($amountA - $amountB) / 2;

        return $subtractionResult < 0 ? 'Vous devez payer ' . abs($subtractionResult) . '€' : 'Vous avez droit à un remboursement de ' . $subtractionResult . '€';

    }

    private function getUserTickets(int $user): ?array
    {
        return $this->em->getRepository(Ticket::class)->findBy([
            'user' => $user,
        ]);
    }

    private function calculateTotalAmount(?array $tickets): float
    {
        if (!$tickets) {
            return 0;
        }

        return array_reduce(
            $tickets,
            fn($sum, $ticket) => $sum + $ticket->getAmount(),
            0
        );
    }
}
