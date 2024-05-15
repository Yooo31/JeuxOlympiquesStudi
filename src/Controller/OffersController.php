<?php

namespace App\Controller;

use App\Repository\OffersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/offers', name: 'offers.')]
class OffersController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(OffersRepository $repository): Response
    {
        $offers = $repository->findBy(['isInactive' => false]);

        return $this->render('offers/index.html.twig', [
            'offers' => $offers,
        ]);
    }
}
