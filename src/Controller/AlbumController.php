<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AlbumController extends AbstractController
{
    /**
     * @Route("/album", name="album_home")
     */
    public function index()
    {
        $user = $this->getUser();
        return $this->render('album/index.html.twig', [
            'controller_name' => 'AlbumController',
            'user' => $user,
        ]);
    }
}
