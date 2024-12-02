<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\TotalAmount;
use App\Entity\User;
use App\Message\SaveAmountMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class TotalAmountController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    #[Route('/archive', name: 'app_archive')]
    public function index(): Response
    {
        $archives = $this->em->getRepository(TotalAmount::class)->findAll();

        return $this->render('total_amount/index.html.twig', [
            'archives' => $archives,
        ]);
    }

}
