<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $user = $this->getUser();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'Lucia',
            'user' => $user,
        ]);
    }

}