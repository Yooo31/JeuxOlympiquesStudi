<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/legacy', name: 'legacy.')]
class LegacyController extends AbstractController
{
    #[Route('/cookie', name: 'cookie')]
    public function cookie(): Response
    {
        return $this->render('legacy/cookie.html.twig');
    }

    #[Route('/privacy', name: 'privacy')]
    public function privacy(): Response
    {
        return $this->render('legacy/privacy.html.twig');
    }

    #[Route('/terms-of-service', name: 'terms-of-service')]
    public function termsOfService(): Response
    {
        return $this->render('legacy/terms-of-service.html.twig');
    }
}
