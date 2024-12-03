<?php
namespace App\Command;

use App\Entity\Ticket;
use App\Entity\TotalAmount;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:save-amount',
    description: 'calculate and save total amount for each user'
)]
class SaveAmountCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }


    protected function configure(): void
    {
        $this->setDescription('Calcule et sauvegarde les montants totaux pour chaque utilisateur.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
// Calcul du total des montants pour chaque utilisateur
        $userAmount = $this->getUsersTotal();


// Sauvegarde des montants totaux
        foreach ($userAmount as $data) {
            $totalAmount = new TotalAmount();
            $totalAmount->setUser($data['user']);
            $totalAmount->setTotal($data['totalAmount']);
            $totalAmount->setDate(new \DateTimeImmutable('now'));
            $this->em->persist($totalAmount);
        }

        $this->em->flush();

// Suppression de tous les tickets
        $this->removeAllTickets();

        $output->writeln('Les montants ont été sauvegardés et tous les tickets ont été supprimés.');

        return Command::SUCCESS;
    }

// Récupérer le total des montants pour chaque utilisateur
    private function getUsersTotal(): array
    {
        $homeUsers = $this->em->getRepository(User::class)->findAll();

        $userAmount = [];

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

// Supprimer tous les tickets
    private function removeAllTickets(): void
    {
        $tickets = $this->em->getRepository(Ticket::class)->findAll();
        foreach ($tickets as $ticket) {
            $this->em->remove($ticket);
        }
        $this->em->flush();
    }
}
