<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/account', name: 'account.')]
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }

    #[Route('/send-verification', name: 'send_verification')]
    public function sendVerificationEmail(UserRepository $user, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($user->isVerified()) {
            $this->addFlash('warning', 'Your account is already verified.');

            return $this->redirectToRoute('account.index');
        }

        $user->setVerified(true);
        $em->flush();

        return $this->redirectToRoute('account.index');
    }

    #[Route('/tickets', name: 'tickets')]
    public function tickets(TicketRepository $tickets): Response
    {
        $user = $this->getUser();
        $allTickets = $tickets->findBy(['user' => $user]);
        // dd($allTickets);

        return $this->render('account/tickets.html.twig', [
            'tickets' => $allTickets,
        ]);
    }
}
