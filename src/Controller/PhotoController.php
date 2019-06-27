<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Album;
use App\Entity\UserAlbum;
use App\Entity\Photo;
use App\Entity\Mosaic;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\PhotoRepository;



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

    /**
     * @Route("/photo/create", name="create_album_from_photos")
     * @todo Check if photo belongs to the right user
     */
    public function createAlbum(Request $request, ObjectManager $em)
    {
    	$photo_ids = $request->request->get('photos');
    	if (! $photo_ids) {
    		return new JsonResponse(array('success' => false));
    	}

    	$user = $this->getUser();
    	$album = new Album();
    	$album->setTitle("Nouvel album");
    	$userAlbum = new UserAlbum();
    	$userAlbum->setAlbum($album)
    		->setUser($user)
    		->setIsEditable(true);
    	$em->persist($album);
    	$em->persist($userAlbum);
    	
    	foreach ($photo_ids as $id) {
    		$photo = $this->getDoctrine()->getRepository(Photo::class)->find($id);
    		$mosaic = new Mosaic();
    		$mosaic->setPhoto($photo)->setAlbum($album);
    		$em->persist($mosaic);
    	}
    	
    	$em->flush();
    	return new JsonResponse(array('success' => true, 'album' => $userAlbum->getId() ));
    }
}
