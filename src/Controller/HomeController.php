<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Friendship;
use App\Form\FriendShipFormType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, UserRepository $repo, ObjectManager $manager)
    {
        $user = $this->getUser();
        $allFriends = null;
        if ($user) {
            $allFriends = $user->getFriends();
        }

        return $this->render('home/index.html.twig', [
            'allFriends' => $allFriends,
            //'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/friend/add", name="friend_create")
     */
    public function addFriend(Request $request, ObjectManager $manager, UserRepository $repo)
    {
        $email = $request->request->get("email");
        $user = $this->getUser();

        if (!empty($email)) {
            $isFriendInDb = $repo->findOneBy(['email' => $email]);
            if ($isFriendInDb) {
                $friendship = new Friendship();
                $friendship->setFriend($isFriendInDb)
                    ->setuser($user);

                $friendshipForFriend = new Friendship();
                $friendshipForFriend->setFriend($user)
                    ->setuser($isFriendInDb);

                $manager->persist($friendship);
                $manager->persist($friendshipForFriend);
                $manager->flush();
                $this->addFlash('success', 'Ami ajouté');
            } else {
                $this->addFlash('warning', 'Ami non trouvé');
            }
        }
        return $this->redirectToRoute('home');
    }

}