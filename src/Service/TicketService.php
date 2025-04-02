<?php

namespace App\Service;

use App\Entity\Home;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Exception;
use Dompdf\Options;

class TicketService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function subtractionOfTicketsAmount(User $connectedUser): string|float
    {
        $homeId = $connectedUser->getHome();

        $homeUsers = $this->em->getRepository(User::class)->findBy(['home' => $homeId]);

        if (2 !== count($homeUsers)) {
            return '';
        }

        $userAmount = [];
        $totalConnectedUserTickets = $this->calculateTotalAmount($connectedUser->getTickets()->toArray());

        foreach ($homeUsers as $user) {
            $userAmount[] = $this->getUserTickets($user);
        }

        if ($totalConnectedUserTickets === $userAmount[0]) {
            $subtractionResult = ($userAmount[0] - $userAmount[1]) / 2;
        } else {
            $subtractionResult = ($userAmount[1] - $userAmount[0]) / 2;
        }

        return $subtractionResult;
    }

    private function getUserTickets(User $user): float
    {
        // Utilise la méthode getUserTickets pour obtenir le montant pour chaque utilisateur
        $tickets = $this->em->getRepository(Ticket::class)->findBy(['user' => $user]);

        // Calcule et retourne le montant total des tickets pour un utilisateur
        return array_reduce(
            $tickets,
            fn ($sum, $ticket) => $sum + $ticket->getAmount(),
            0
        );
    }

    /**
     * @param Ticket[]|null $tickets
     */
    private function calculateTotalAmount(?array $tickets): float
    {
        if (!$tickets) {
            return 0;
        }

        return array_reduce(
            $tickets,
            fn ($sum, $ticket) => $sum + $ticket->getAmount(),
            0
        );
    }

    public function generateCsvForHome(Home $home): ?string
    {
        $users = $home->getUsers();

        if ($users->isEmpty()) {
            return null;
        }

        // Récupérer tous les tickets avant suppression
        $tickets = $this->em->getRepository(Ticket::class)->createQueryBuilder('t')
            ->where('t.user IN (:users)')
            ->setParameter('users', $users)
            ->getQuery()
            ->getResult();

        if (empty($tickets)) {
            return null;
        }

        $filename = '/tmp/tickets_home_'.$home->getId().'_'.date('Y-m').'.csv';
        $handle = fopen($filename, 'w');
        if (false === $handle) {
            throw new Exception('Impossible d\'ouvrir le fichier');
        }

        // En-têtes
        fputcsv($handle, ['ID', 'Date', 'Montant', 'Utilisateur']);

        foreach ($tickets as $ticket) {
            fputcsv($handle, [
                $ticket->getId(),
                $ticket->getCreatedAt()->format('Y-m-d H:i:s'),
                number_format($ticket->getAmount(), 2, ',', ' '),
                $ticket->getUser()->getUsername(),
            ]);
        }

        fclose($handle);

        return $filename;
    }

    public function generatePdfForHome(Home $home): ?string
    {
        $users = $home->getUsers();

        if ($users->isEmpty()) {
            return null;
        }

        // Récupérer les tickets de la Home
        $tickets = $this->em->getRepository(Ticket::class)->createQueryBuilder('t')
            ->where('t.user IN (:users)')
            ->setParameter('users', $users)
            ->getQuery()
            ->getResult();

        if (empty($tickets)) {
            return null;
        }

        // Options Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        // Construction du contenu HTML du PDF
        $html = '<h1>Tickets de caisse</h1>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">';
        $html .= '<tr><th>Nom</th><th>Date</th><th>Montant</th><th>Utilisateur</th></tr>';

        foreach ($tickets as $ticket) {
            $html .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s €</td><td>%s</td></tr>',
                $ticket->getTitle(),
                $ticket->getCreatedAt()->format('Y-m-d H:i:s'),
                number_format($ticket->getAmount(), 2, ',', ' '),
                $ticket->getUser()->getUsername()
            );
        }

        $html .= '</table>';

        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Enregistrer le fichier PDF temporairement
        $pdfPath = '/tmp/tickets_home_'.$home->getId().'_'.date('Y-m').'.pdf';
        file_put_contents($pdfPath, $dompdf->output());

        return $pdfPath;
    }
}
