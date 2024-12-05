<?php
namespace App\Command;

use App\Entity\Home;
use App\Entity\Ticket;
use App\Entity\TotalAmount;
use App\Entity\User;
use App\Service\MailService;
use App\Service\TicketService;
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
    private MailService $mail;
    private TicketService $ticketService;

    public function __construct(EntityManagerInterface $em, MailService $mail, TicketService $ticketService)
    {
        $this->em = $em;

        parent::__construct();
        $this->mail = $mail;
        $this->ticketService = $ticketService;
    }


    protected function configure(): void
    {
        $this->setDescription('Calcule et sauvegarde les montants totaux pour chaque utilisateur.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->em->getRepository(User::class)->findAll();
        foreach ($users as $user){
            $due = $this->ticketService->subtractionOfTicketsAmount($user);
            $home = $this->em->getRepository(Home::class)->find($user->getHome()->getId());
            $usersHome = $home->getUsers();
            foreach ($usersHome as $userHome){
                if ($userHome->getUsername() !== $user->getUsername()){
                    $secondUser = ($userHome->getUsername());
                }
            }
            $this->mail->sendMail(
                user: $user,
                subject: 'Tickets : Récapitulatif du mois',
                template: 'notification',
                context: [
                    'username' => $user->getUsername(),
                    'secondUser' => $secondUser,
                    'due' => $due
                ]
            );
        }


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

// Envoi du mail


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
                'user' => $user,
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