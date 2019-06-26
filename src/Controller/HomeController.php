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
        $users = array("lu", "so", "ju", "adr");
        $age = 17;
        $user = $this->getUser();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'Lucia',
            'user' => $user,
            'age'=> $age,
            'users' =>$users
        ]);
    }


}