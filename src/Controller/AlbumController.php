<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function createAlbum()
    {
        $user = $this->getUser();
        $album = new Album();
        $album->setTitle("Nouvel Album 6");
        $userAlbum = new UserAlbum();
        $userAlbum->setAlbum($album)
            ->setUser($user)
            ->setIsEditable(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($album);
        $em->persist($userAlbum);
        $em->flush();
        return $this->redirectToRoute('album_view', array('id' => $userAlbum->getId()));
    }

    /**
     * @Route("/album/{id}", name="album_view")
     */
    public function viewAlbum($id="")
    {
        $user = $this->getUser();
        if($id=="" || !is_numeric($id))
            return $this->redirectToRoute('album_home');
        $repo = $this->getDoctrine()->getRepository(UserAlbum::class);
        $userAlbum = $repo->find(\intval($id));
        if ($userAlbum->getUser()->getId() != $user->getId() || !$userAlbum->getIsEditable()){
            $this->addFlash('warning', "Vous n'êtes pas autorisé à effectuer cette action");
            return $this->redirectToRoute('album_home');
        }
        return $this->render('album/view.html.twig', [
            'album' => $userAlbum->getAlbum(),
        ]);
    }
}
