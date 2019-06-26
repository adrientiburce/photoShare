<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Notification\RegisterNotification;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    /** @var UserPasswordEncoderInterface * */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/inscription", name="register")
     */
    public function registration(ObjectManager $manager, Request $request, UserRepository $repo)
    {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $isUserInDatabase = $repo->findBy(['email' => $user->getEmail()]);

            if($isUserInDatabase != null){
                $this->addFlash(
                    'warning',
                    "L'adresse email est déjà utitlisé"
                );
                return $this->redirectToRoute('register');
            }
            $hash = $this->passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setCreatedAt(new \DateTime());
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Utilisateur créé avec succés ');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        return $this->redirectToRoute('home');
    }
}
