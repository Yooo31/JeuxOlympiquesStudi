<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // generate Username with first_name + last_name
            $username = $user->getFirstName() . $user->getLastName();

            // check if username already exists
            $usernameExists = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

            // if username already exists, add +1 to the number at end of the last username. Exemple : johnDoe1, johnDoe2, johnDoe3, ...
            if ($usernameExists) {
                $username = $username . '1';
                $usernameExists = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
                while ($usernameExists) {
                    $username = substr($username, 0, -1) . ((int)substr($username, -1) + 1);
                    $usernameExists = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
                }
            }

            $user->setUsername($username);

            // generate a random token for the user
            $user->setAccountKey(bin2hex(random_bytes(32)));

            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            try {
                // Générer un URL signé et l'envoyer par email à l'utilisateur
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('support@jo-studi.fr', 'Support'))
                        ->to($user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('danger', 'Erreur lors de l\'envoi du mail, serveur SMTP inactif. Cliquez sur envoyer un mail de vérification pour confirmer manuellement votre compte après vous être connecté.');
                return $this->redirectToRoute('account.index'); // Rediriger l'utilisateur vers le formulaire d'inscription
            }


            return $security->login($user, AppAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');
        return $this->redirectToRoute('app_register');
    }
}
