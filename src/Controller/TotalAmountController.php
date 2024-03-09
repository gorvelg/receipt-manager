<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\TotalAmount;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TotalAmountController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/total', name: 'app_total_amount')]
    public function saveAmount(): Response
    {
        $userAmount = $this->getUsersTotal();


        for ($i = 0; $i < count($userAmount); $i++) {
            $totalAmount = new TotalAmount();
            $totalAmount->setUser($userAmount[$i]['user']);
            $totalAmount->setTotal($userAmount[$i]['totalAmount']);
            $totalAmount->setDate(new \DateTimeImmutable('now'));
            $this->em->persist($totalAmount);
            $this->em->flush();
        }
        $this->removeAllTickets();

        return $this->render('total_amount/index.html.twig', [

        ]);
    }

    private function getUsersTotal(): array
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

    private function removeAllTickets(): void
    {
        $tickets = $this->em->getRepository(Ticket::class)->findAll();
        foreach ($tickets as $ticket) {
            $this->em->remove($ticket);
        }
        $this->em->flush();
    }


}
