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
            'userAlbumId' => $userAlbum->getId(),
        ]);
    }

    /**
     * @Route("/album/{id}/upload", name="album_upload_photo", requirements={"id"="\d+"})
     */
    public function uploadPhoto($id, UserAlbumRepository $repo, ObjectManager $manager, Request $request)
    {
        $user = $this->getUser();
        $userAlbum = $repo->findEditableFromUser($user, $id);
        $image = $request->files->get('file');

        $album = $userAlbum->getAlbum();
        $photo = new Photo();
        $photo->addOwner($user)
            ->setImageFile($image);

        $mosaic = new Mosaic();
        $mosaic->setPhoto($photo)
            ->setAlbum($album);

        $manager->persist($photo);
        $manager->persist($mosaic);
        $manager->flush();


        // return $this->redirectToRoute('album_home');
        return new JsonResponse([
            'success' => true,
            'src' => $photo->getImageName()
        ]);
    }

    /**
     * @Route("/album/{id}/add", name="add_photo_to_album", requirements={"id"="\d+"})
     */
    public function addPhoto($id, Request $request, ObjectManager $em, UserAlbumRepository $repo)
    {
        $user = $this->getUser();
        $userAlbum = $repo->findEditableFromUser($user, $id);
        $album = $userAlbum->getAlbum();

        $photo_ids = $request->request->get('photos');
        if (! $photo_ids) {
            return new JsonResponse(array('success' => false));
        }
     
        foreach ($photo_ids as $id) {
            $photo = $this->getDoctrine()->getRepository(Photo::class)->find($id);
            $mosaic = new Mosaic();
            $mosaic->setPhoto($photo)->setAlbum($album);
            $em->persist($mosaic);
        }
        
        $em->flush();
        return new JsonResponse(array('success' => true, 'album' => $userAlbum->getId() ));
    }


    /**
     * @Route("/album/{id}/titre", name="album_set_title", requirements={"id"="\d+"})
     */
    public function setTitle($id, Request $request, UserAlbumRepository $repo, ObjectManager $em)
    {
        $newTitle = $request->request->get('title');
        $user = $this->getUser();
        $userAlbum = $repo->findEditableFromUser($user, $id);
        $album = $userAlbum->getAlbum();
        $newTitle = ($newTitle == "") ? "Album sans titre" : $newTitle;
        $album->setTitle($newTitle);
        $em->persist($album);
        $em->flush();

        return new JsonResponse(array('success' => true));
    }





}
