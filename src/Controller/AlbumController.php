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
use App\Entity\Photo;
use App\Entity\Mosaic;
use Symfony\Component\HttpFoundation\JsonResponse;


class AlbumController extends AbstractController
{
    /**
     * @Route("/album", name="album_home")
     */
    public function index()
    {
        $user = $this->getUser();
        return $this->render('album/index.html.twig', [
            'user' => $user
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
    public function viewAlbum(UserAlbumRepository $repo, $id)
    {
        $user = $this->getUser();
        $userAlbum = $repo->findEditableFromUser($user, $id);

        if ($userAlbum == null) {
            $this->addFlash('warning', "Vous n'êtes pas autorisé à effectuer cette action");
            return $this->redirectToRoute('album_home');
        }
        return $this->render('album/view.html.twig', [
            'album' => $userAlbum->getAlbum(),
            'id' => $userAlbum->getId(),
        ]);
    }

    /**
     * @Route("/album/{id}/upload", name="album_upload_photo", requirements={"id"="\d+"})
     */
    public function uploadPhoto(UserAlbumRepository $repo, $id, Request $request)
    {
        $user = $this->getUser();
        $userAlbum = $repo->findEditableFromUser($user, $id);
        $image = $request->files->get('file');
        $album = $userAlbum->getAlbum();
        $photo = new Photo();
        $photo->addOwner($user);
        $photo->setImageFile($request->files->get('file'));
        $mosaic = new Mosaic();
        $mosaic->setPhoto($photo)->setAlbum($album);
        $em = $this->getDoctrine()->getManager();
        $em->persist($photo);
        $em->persist($mosaic);
        $em->flush();


        // return $this->redirectToRoute('album_home');
        return new JsonResponse(array('success' => true, "src"=>$photo->getImageName()));
    }


}
