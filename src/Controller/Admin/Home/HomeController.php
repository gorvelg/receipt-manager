<?php

namespace App\Controller\Admin\Home;

use App\Entity\Home;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/admin/create-home', name: 'create_home')]
    public function createHome(): Response
    {
// Créer un nouvel objet Home
        $home = new Home();

// Enregistrer dans la base de données
        $this->em->persist($home);
        $this->em->flush();

// Afficher un message de succès et rediriger vers une autre page
        $this->addFlash('success', 'Home créé avec succès!');

// Redirige vers une page de succès ou la page d'accueil
        return $this->redirectToRoute('app_admin_get_user');
    }
}
