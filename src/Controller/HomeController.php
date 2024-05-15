<?php

namespace App\Controller;

use App\Repository\OffersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'home.')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, OffersRepository $repository): Response
    {
        $offers = $repository->findBy(['isInactive' => false], ['createdAt' => 'ASC'], 3);

        return $this->render('home/index.html.twig', [
            'offers' => $offers,
        ]);
    }
}
