<?php

namespace App\Controller;

use App\Form\AlbumType;
use App\Repository\UserAlbumRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Album;
use App\Entity\UserAlbum;


class AlbumController extends AbstractController
{
    /**
     * @Route("/album", name="album_home")
     */
    public function index()
    {
        $user = $this->getUser();
        return $this->render('album/index.html.twig', [
        ]);
    }

    /**
     * @Route("/album/new", name="album_create")
     */
    public function createAlbum(Request $request, ObjectManager $manager)
    {
        $user = $this->getUser();
        $album = new Album();

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // classe pour lier l'album au user
            $userAlbum = new UserAlbum();
            $userAlbum->setAlbum($album)
                ->setUser($user)
                ->setIsEditable(true);
            //$em = $this->getDoctrine()->getManager();
            $manager->persist($album);
            $manager->persist($userAlbum);
            $manager->flush();
            return $this->redirectToRoute('album_view', [
                'id' => $userAlbum->getId()
            ]);
        }


        return $this->render('album/create.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/album/{id}", name="album_view", requirements={"id"="\d+"})
     */
    public function viewAlbumBis(UserAlbumRepository $repo, $id)
    {
        $user = $this->getUser();
        $userAlbum = $repo->findEditableFromUser($user, $id);

        if ($userAlbum == null) {
            $this->addFlash('warning', "Vous n'êtes pas autorisé à effectuer cette action");
            return $this->redirectToRoute('album_home');
        }
        return $this->render('album/view.html.twig', [
            'album' => $userAlbum->getAlbum(),
        ]);
    }

//    /**
//     * @Route("/album/{id}", name="album_view")
//     */
//    public function viewAlbum($id="")
//    {
//        $user = $this->getUser();
//        if($id=="" || !is_numeric($id))
//            return $this->redirectToRoute('album_home');
//        $repo = $this->getDoctrine()->getRepository(UserAlbum::class);
//        $userAlbum = $repo->find(\intval($id));
//        if ($userAlbum->getUser()->getId() != $user->getId() || !$userAlbum->getIsEditable()){
//            $this->addFlash('warning', "Vous n'êtes pas autorisé à effectuer cette action");
//            return $this->redirectToRoute('album_home');
//        }
//        return $this->render('album/view.html.twig', [
//            'album' => $userAlbum->getAlbum(),
//        ]);
//    }


}
