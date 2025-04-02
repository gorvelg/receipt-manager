<?php

namespace App\Controller\Admin\Home;

use App\Entity\Home;
use App\Form\HomeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/admin/create-home', name: 'app_create_home')]
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

    #[Route('/admin/get/home', name: 'app_admin_get_home')]
    public function getHome(): Response
    {
        $homes = $this->em->getRepository(Home::class)->findAll();

        return $this->render('admin/home/index.html.twig', [
            'homes' => $homes,
        ]);
    }

    #[Route('/admin/set/home/{home}', name: 'app_admin_set_home')]
    public function setHome(Request $request, Home $home): Response
    {
        $form = $this->createForm(HomeType::class, $home);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($home);
            $this->em->flush();
            $this->addFlash('success', 'Foyer mis à jour avec succès!');

            return $this->redirectToRoute('app_admin_get_home');
        }

        return $this->render('admin/home/set.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
