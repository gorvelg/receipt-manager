<?php

namespace App\MessageHandler;

use App\Entity\Ticket;
use App\Entity\TotalAmount;
use App\Entity\User;
use App\Message\SaveAmountMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SaveAmountMessageHandler
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke(SaveAmountMessage $message)
    {
        $userAmount = $this->getUsersTotal();

        for ($i = 0; $i < count($userAmount); $i++) {
            $totalAmount = new TotalAmount();
            $totalAmount->setUser($userAmount[$i]['user']);
            $totalAmount->setTotal($userAmount[$i]['totalAmount']);
            $totalAmount->setDate(new \DateTimeImmutable('now'));
            $this->em->persist($totalAmount);
        }
            $this->em->flush();
            $this->removeAllTickets();
    }

    public function getUsersTotal(): array
    {
        $homeUsers = $this->em->getRepository(User::class)->findBy(['home' => 1]);

        foreach ($homeUsers as $user) {
            $tickets = $this->em->getRepository(Ticket::class)->findBy(['user' => $user]);
            // Calcule et retourne le montant total des tickets pour un utilisateur
            $totalAmount = array_reduce(
                $tickets,
                fn($sum, $ticket) => $sum + $ticket->getAmount(),
                0
            );

            $userAmount[] = [
                'user' => $user->getUsername(),
                'totalAmount' => $totalAmount,
            ];
        }
        return $userAmount;
    }

    public function removeAllTickets(): void
    {
        $tickets = $this->em->getRepository(Ticket::class)->findAll();
        foreach ($tickets as $ticket) {
            $this->em->remove($ticket);
        }
        $this->em->flush();
    }
}
