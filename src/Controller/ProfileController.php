<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Form\Model\ChangePassword;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile_home")
     */
    public function index(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        // handle Form for P A S S W O R D

        $userPassword = new ChangePassword();
        $formPassword = $this->createForm(ChangePasswordType::class, $userPassword)
            ->handleRequest($request);
        $isPassModalOpen = false;
        if ($formPassword->isSubmitted() && !$formPassword->isValid()) {
            $isPassModalOpen = true;
        }
        if ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $this->addFlash('success', 'Mot de Passe mis à jour avec succès');
            $hash = $encoder->encodePassword($user, $userPassword->getPassword());
            $user->setPassword($hash);

            $manager->flush();
            return $this->redirectToRoute('profile_home');
        }
        return $this->render('profile/index.html.twig', [
            'isPassModalOpen' => $isPassModalOpen,
            'formPassword' => $formPassword->createView(),
        ]);
    }
}
