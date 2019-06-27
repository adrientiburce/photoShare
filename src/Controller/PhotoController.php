<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Photo;
use Symfony\Component\HttpFoundation\JsonResponse;


class PhotoController extends AbstractController
{
    /**
     * @Route("/photo", name="photo_home")
     */
    public function index()
    {
    	$user = $this->getUser();
        return $this->render('photo/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/photo/upload", name="photo_upload")
     */
    public function uploadPhoto(Request $request){
    	$user = $this->getUser();
    	$image = $request->files->get('file');
    	$photo = new Photo();
    	$photo->addOwner($user);
    	$photo->setImageFile($image);
    	$em = $this->getDoctrine()->getManager();
    	$em->persist($photo);
    	$em->flush();
    	return new JsonResponse(array('success' => true, "src"=>$photo->getImageName()));
    }
}
