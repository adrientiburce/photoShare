<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AlbumType;
use App\Form\UserAlbumType;
use App\Repository\AlbumRepository;
use App\Repository\FriendshipRepository;
use App\Repository\UserAlbumRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            'user' => $user,
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
                ->setIsEditable(true)
                ->setIsOwner(true);
            $manager->persist($album);
            $manager->persist($userAlbum);
            $manager->flush();

            $this->addFlash("success", "Album créé, cliquer sur le titre pour le modifer");
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
    public function viewAlbum($id, UserAlbumRepository $userAlbumRepo, Request $request, ObjectManager $manager)
    {
        $user = $this->getUser();
        $userAlbum = $userAlbumRepo->findEditableFromUser($user, $id);
        if ($userAlbum == null) {
            $this->addFlash('warning', "Vous n'êtes pas autorisé à effectuer cette action");
            return $this->redirectToRoute('album_home');
        }
        dump($userAlbum);

        $allUserAlbum = $userAlbumRepo->findAllShare($userAlbum->getAlbum());
        foreach ($allUserAlbum as $k => $userAlbumValue) {
            if ($userAlbumValue->getIsOwner()) {
                unset($allUserAlbum[$k]);
            }
        }

        $newUserAlbum = new UserAlbum();
        $form = $this->createForm(UserAlbumType::class, $newUserAlbum);
        // we find all friends of our user
        $form->add('user', EntityType::class, [
            'class' => User::class,
            'label' => 'Choisir parmis vos amis',
            'choice_label' => 'email',
            'required' => true,
            'query_builder' => function (UserRepository $repo) {
                $user = $this->getUser();
                return $repo->findAllFriends($user);
            },
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $isFriendUserAlbum = $userAlbumRepo->findOneBy([
                'album' => $userAlbum->getAlbum(),
                'user' => $newUserAlbum->getUser(),
            ]);
            if ($isFriendUserAlbum == null) {
                $newUserAlbum->setAlbum($userAlbum->getAlbum());
                $newUserAlbum->setIsOwner(false);

                $this->addFlash('success', "Album partagé");
                $manager->persist($newUserAlbum);
                $manager->flush();
            } else {
                $this->addFlash('warning', "Album déjà partagé avec cet ami");
            }
            return $this->redirectToRoute('album_view', [
                'id' => $userAlbum->getId(),
            ]);
        }

        return $this->render('album/view.html.twig', [
            'userAlbum' => $userAlbum,
            'album' => $userAlbum->getAlbum(),
            'userAlbumId' => $userAlbum->getId(),
            'allUserAlbum' => $allUserAlbum,
            'form' => $form->createView()
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
        if (!$photo_ids) {
            return new JsonResponse(array('success' => false));
        }

        foreach ($photo_ids as $id) {
            $photo = $this->getDoctrine()->getRepository(Photo::class)->find($id);
            $mosaic = new Mosaic();
            $mosaic->setPhoto($photo)->setAlbum($album);
            $em->persist($mosaic);
        }

        $em->flush();
        return new JsonResponse(array('success' => true, 'album' => $userAlbum->getId()));
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

    /**
     * @Route("/album/delete/{id}", name="user_album_delete", methods="DELETE", requirements={"id"="\d+"})
     */
    public function removeUserAlbum(Request $request, UserAlbum $userAlbum, ObjectManager $manager, UserAlbumRepository $userAlbumRepo)
    {
        $userAlbumIdFromOwner = $request->request->get('current_user_album');
        $manager->remove($userAlbum);

        $this->addFlash('success', "Partage de l'albmum supprimé");
        $manager->flush();

        return $this->redirectToRoute('album_view', [
            'id' => $userAlbumIdFromOwner
        ]);
    }

    /**
     * @Route("/album/update/{id}", name="user_album_update", methods="POST", requirements={"id"="\d+"})
     */
    public function changeUserAlbum(Request $request, UserAlbum $userAlbum, ObjectManager $manager)
    {
        $userAlbumIdFromOwner = $request->request->get('current_user_album');
        $oldEdition = $userAlbum->getIsEditable();
        $userAlbum->setIsEditable(!$oldEdition);

        $manager->flush();

        return $this->redirectToRoute('album_view', [
            'id' => $userAlbumIdFromOwner,
        ]);
    }


}
