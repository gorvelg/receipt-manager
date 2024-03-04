<?php
namespace App\Controller;


use App\Repository\HomeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeUsersController extends AbstractController
{


    private HomeRepository $homeRepository;

    public function __construct(HomeRepository $homeRepository)
    {

        $this->homeRepository = $homeRepository;
    }

    #[Route('/foyer', name: 'app_home_users')]
    public function index(): Response
    {

        $connectedUserId = $this->getUser()->getId();

        $homes = $this->homeRepository->findHomesByUser($connectedUserId);

        $usersByFoyer = [];

        foreach ($homes as $home) {
            $foyerUsers = $home->getUsers();

            if (!$foyerUsers->isInitialized()) {
                $foyerUsers->initialize();
            }

            $usersByFoyer[$home->getName()] = $foyerUsers->toArray();
        }






//        $user = $this->getUser();
//
//            if (!$user) {
//
//            }
//
//            $homes = $user->getFoyer();
//
//
//            $usersInHomes = [];
//
//            foreach ($homes as $home) {
//                $usersCollection = $home->getUsers();
//
//                if (!$usersCollection->isInitialized()) {
//                    $usersCollection->initialize();
//                }
//
//                $usersInHomes = array_merge($usersInHomes, $usersCollection->toArray());
//            }


        return $this->render('home_users/index.html.twig', [
            'usersByFoyer' => $usersByFoyer,

        ]);
    }
}
