<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\PaymentUserService;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin.')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(PaymentUserService $paymentUserService): Response
    {
        $paymentCount = $paymentUserService->getPaymentCount();
        $userCount = $paymentUserService->getUserCount();
        $verifiedUserCount = $paymentUserService->getVerifiedUserCount();

        return $this->render('admin/index.html.twig', [
            'paymentCount' => $paymentCount,
            'userCount' => $userCount,
            'verifiedUserCount' => $verifiedUserCount
        ]);
    }

    #[Route('/decode', name: 'decode')]
    public function decode(): Response
    {
        return $this->render('admin/.html.twig');
    }

    #[Route('/stats', name: 'stats')]
    public function stats(): Response
    {
        return $this->render('admin/.html.twig');
    }

    #[Route('/offers', name: 'offers')]
    public function offers(): Response
    {
        return $this->render('admin/.html.twig');
    }
}
