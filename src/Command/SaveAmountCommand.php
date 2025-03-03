<?php

namespace App\Command;

use App\Entity\Home;
use App\Entity\Ticket;
use App\Entity\TotalAmount;
use App\Service\MailService;
use App\Service\TicketService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:save-amount',
    description: 'Calculate and save total amount for each user if cronDay matches today.'
)]
class SaveAmountCommand extends Command
{
    private EntityManagerInterface $em;
    private MailService $mail;
    private TicketService $ticketService;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        EntityManagerInterface $em,
        MailService $mail,
        TicketService $ticketService,
        ParameterBagInterface $parameterBag
    ) {
        parent::__construct();
        $this->em = $em;
        $this->mail = $mail;
        $this->ticketService = $ticketService;
        $this->parameterBag = $parameterBag;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        // Récupère toutes les `Home`
        $homes = $this->em->getRepository(Home::class)->findAll();
        if (!$homes) {
            $output->writeln('<error>Aucune home trouvée.</error>');
            return Command::FAILURE;
        }

        foreach ($homes as $home) {
            $cronDay = $home->getCronDay();

            // Vérifie si le cronDay correspond à aujourd'hui
            if ($cronDay === (int)$today->format('d')) {
                $usersHome = $home->getUsers();

                if (count($usersHome) !== 2) {
                    $output->writeln(sprintf('<comment>La Home ID %d ne contient pas exactement 2 utilisateurs.</comment>', $home->getId()));
                    continue;
                }

                $users = $usersHome->toArray();

                $pdfPath = $this->ticketService->generatePdfForHome($home);


                // Calcul des montants dus et envoi des emails
                foreach ($users as $currentUser) {
                    $otherUser = $users[0] === $currentUser ? $users[1] : $users[0];
                    $due =
                        (
                            $this->ticketService->subtractionOfTicketsAmount($currentUser)
                            - $this->ticketService->subtractionOfTicketsAmount($otherUser)
                        ) / 2;

                    $this->mail->sendMail(
                        user: $currentUser,
                        subject: 'Tickets : Récapitulatif du mois',
                        template: 'notification',
                        context: [
                            'username' => $currentUser->getUsername(),
                            'secondUser' => $otherUser->getUsername(),
                            'due' => $due
                        ],
                        attachment: $pdfPath ? ['path' => $pdfPath, 'name' => 'Tickets_' . date('Y-m') . '.pdf'] : null
                    );
                }

                $output->writeln(sprintf('<info>Emails envoyés pour la Home ID %d.</info>', $home->getId()));

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

                // Supprimer les images et les tickets pour cette home
                $this->removeImageTicketsForHome($home);
                $this->removeTicketsForHome($home);
            }
        }

        $output->writeln('<info>Les montants ont été calculés et sauvegardés, et les tickets pertinents ont été supprimés.</info>');
        return Command::SUCCESS;
    }

    private function getUserTotal(Home $home): array
    {
        $userAmount = [];
        foreach ($home->getUsers() as $user) {
            $tickets = $this->em->getRepository(Ticket::class)->findBy(['user' => $user]);
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

    private function removeImageTicketsForHome(Home $home): void
    {
        $picturesDirectory = $this->parameterBag->get('pictures_directory');

        foreach ($home->getUsers() as $user) {
            $tickets = $this->em->getRepository(Ticket::class)->findBy(['user' => $user]);
            foreach ($tickets as $ticket) {
                $pictureName = $ticket->getPhoto();
                if ($pictureName) {
                    $path = $picturesDirectory . DIRECTORY_SEPARATOR . $pictureName;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }
        }
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
