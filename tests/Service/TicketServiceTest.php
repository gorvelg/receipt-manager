<?php

namespace App\Tests\Service;

use App\Entity\Home;
use App\Entity\Ticket;
use App\Entity\User;
use App\Service\TicketService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class TicketServiceTest extends TestCase
{
    public function testSubtractionOfTicketsAmountWithTwoUsers(): void
    {
        // 🎟️ Tickets simulés
        $ticket1 = $this->createConfiguredMock(Ticket::class, [
            'getAmount' => 10.0,
        ]);

        $ticket2 = $this->createConfiguredMock(Ticket::class, [
            'getAmount' => 20.0,
        ]);

        // 🏠 Home simulé
        $home = $this->createMock(Home::class);

        // 👤 Utilisateur connecté
        $userA = $this->createMock(User::class);
        $userA->method('getHome')->willReturn($home);
        $userA->method('getTickets')->willReturn(new ArrayCollection([$ticket1]));

        // 👤 Autre utilisateur
        $userB = $this->createMock(User::class);
        $userB->method('getTickets')->willReturn(new ArrayCollection([$ticket2]));

        // 📦 Mock du repository User
        $userRepo = $this->createMock(EntityRepository::class);
        $userRepo->method('findBy')->with(['home' => $home])->willReturn([$userA, $userB]);

        // 🧠 EntityManager mocké avec getRepository()
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->willReturnCallback(function ($entityClass) use ($userRepo) {
                return match ($entityClass) {
                    User::class => $userRepo,
                    // Ticket repository plus utilisé ici car on passe par $user->getTickets()
                    default => throw new \Exception("Unexpected repository: " . $entityClass),
                };
            });

        // 🧪 Instanciation du service et test
        $service = new TicketService($em);
        $result = $service->subtractionOfTicketsAmount($userA);

        // ✅ (20 - 10) / 2 = 5
        $this->assertEquals(-5.0, $result);
    }
}
