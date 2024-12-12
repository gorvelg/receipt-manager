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
    description: 'Calculate and save total amount for each user if cronDay matches today.'
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
        $this->setDescription('Calcule et sauvegarde les montants totaux pour chaque utilisateur si cronDay correspond à aujourd\'hui.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = new \DateTimeImmutable();

        // Récupère toutes les `Home`
        $homes = $this->em->getRepository(Home::class)->findAll();
        if (!$homes) {
            $output->writeln('Aucune home trouvée.');
            return Command::FAILURE;
        }

        foreach ($homes as $home) {
            $cronDay = $home->getCronDay();

            // Vérifie si le cronDay correspond à aujourd'hui
            if ($cronDay === (int)$today->format('d')) {
                $usersHome = $home->getUsers();

                if (count($usersHome) !== 2) {
                    $output->writeln(sprintf('La Home ID %d ne contient pas exactement 2 utilisateurs.', $home->getId()));
                    continue;
                }

                $users = $usersHome->toArray();

                // Calcul des montants dus et envoi des emails
                foreach ($users as $currentUser) {
                    $otherUser = $users[0] === $currentUser ? $users[1] : $users[0];
                    $due = $this->ticketService->subtractionOfTicketsAmount($currentUser) - $this->ticketService->subtractionOfTicketsAmount($otherUser);

                    $this->mail->sendMail(
                        user: $currentUser,
                        subject: 'Tickets : Récapitulatif du mois',
                        template: 'notification',
                        context: [
                            'username' => $currentUser->getUsername(),
                            'secondUser' => $otherUser->getUsername(),
                            'due' => $due
                        ]
                    );
                }

                $output->writeln(sprintf('Emails envoyés pour la Home ID %d.', $home->getId()));

                $userAmount = $this->getUserTotal($home);

                // Sauvegarde des montants totaux
                foreach ($userAmount as $data) {
                    $totalAmount = new TotalAmount();
                    $totalAmount->setUser($data['user']);
                    $totalAmount->setTotal($data['totalAmount']);
                    $totalAmount->setDate(new \DateTimeImmutable('now'));
                    $this->em->persist($totalAmount);
                }

                $this->em->flush();

                // Supprimer les tickets pour cette home
                $this->removeTicketsForHome($home);
            }
        }

        $output->writeln('Les montants ont été calculés et sauvegardés, et les tickets pertinents ont été supprimés.');
        return Command::SUCCESS;
    }

    private function getUserTotal(Home $home): array
    {

        $userAmount = [];
        foreach ($home->getUsers() as $user) {
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
    private function removeTicketsForHome(Home $home): void
    {
        foreach ($home->getUsers() as $user) {
            $tickets = $this->em->getRepository(Ticket::class)->findBy(['user' => $user]);
            foreach ($tickets as $ticket) {
                $this->em->remove($ticket);
            }
        }
        $this->em->flush();
    }
}
