<?php

namespace App\Controller;

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

    #[Route('/archive', name: 'app_archive')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non valide.');
        }

        $home = $user->getHome();


        if (empty($home)) {
            $this->addFlash('danger', 'L\'utilisateur n\'a pas de Home attribué.');

            return $this->render('errors/error.html.twig', [
            ]);
        }

        $homeId = $home->getId();

        $usersInHome = $this->em->getRepository(User::class)->findBy(['home' => $homeId]);
        $userIds = array_map(fn ($user) => $user->getId(), $usersInHome);

        $archives = $this->em->getRepository(TotalAmount::class)->findBy([
            'user' => $userIds,
        ]);

        return $this->render('total_amount/index.html.twig', [
            'archives' => $archives,
        ]);
    }
}
