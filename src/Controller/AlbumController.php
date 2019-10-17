<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Mosaic;
use App\Entity\Photo;
use App\Entity\User;
use App\Entity\UserAlbum;
use App\Form\AlbumType;
use App\Form\UserAlbumType;
use App\Repository\UserAlbumRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use ZipArchive;


/**
 * @IsGranted("ROLE_USER")
 */
class AlbumController extends AbstractController
{
    const CAR_IN = array(
        'coffre_ouvert' => 36,
        'commande_volant_droit' => 27,
        'commande_volant_gauche' => 26,
        'compteur' => 25,
        'banquette_arriere' => 33,
        'banquette_arriere_jambes' => 34,
        'boite_a_gant_ouverte' => 32,
        'levier_de_vitesse' => 30,
        'moteur' => 35,
        'gps_multimedia' => 31,
        'roue_de_secours' => 37,
        'tableau_bord_epaule_conducteur' => 22,
        'tableau_bord_epaule_passager' => 28,
        'ensemble_tableau_bord' => 23,
        'toit_ouvrant' => 41,
        'transversal_conducteur' => 21,
        'transversale_cote_passager' => 29,
        'focus_volant' => 24,
    );

    const CAR_OUT = array(
        'face_capot' => 2,
        'jante_arriere_droit' => 16,
        'jante_arriere_gauche' => 14,
        'jante_avant_droit' => 15,
        'jante_avant_gauche' => 13,
        'profil_arriere_droit' => 5,
        'profil_arriere_gauche' => 7,
        'avant_droit' => 3,
        'avant_gauche' => 1,
        'aile_arriere_droite' => 12,
        'aile_arriere_gauche' => 10,
        'aile_avant_droite' => 11,
        'aile_avant_gauche' => 9,
        'arriere_coffre_ferme' => 6,
        'focus_marque' => 39,
        'focus_modele' => 40,
        'porte_arriere_droite' => 20,
        'porte_arriere_gauche' => 18,
        'porte_avant_droite' => 19,
        'porte_avant_gauche' => 17,
        'profil_droit' => 4,
        'profil_gauche' => 8
    );

    const CAR_PHOTOS = array(
        // VOITURE INTERIEUR
        "Intérieur " => self::CAR_IN,
        // VOITURE EXTERIEUR
        "Extérieur " => self::CAR_OUT,

//        // PAPIERS
//        'carnet_entretien_1' => 52,
//        'carnet_entretien_2' => 53,
//        'carnet_entretien_3' => 54,
//        'carnet_entretien_4' => 55,
//        'carnet_entretien_5' => 56,
//        'certificat_immatriculation' => 1,
//        'cle_carnet_entretien' => 38,
//        'controle_technique' => 2,
//        'facture_entretien_1' => 4,
//        'facture_entretien_2' => 5,
//        'facture_entretien_3' => 6,
//        'facture_entretien_4' => 7,
//
//        // LIBRE
//        'photo_libre_1' => 42,
//        'photo_libre_10' => 51,
//        'photo_libre_2' => 43,
//        'photo_libre_3' => 44,
//        'photo_libre_4' => 45,
//        'photo_libre_5' => 46,
//        'photo_libre_6' => 47,
//        'photo_libre_7' => 48,
//        'photo_libre_8' => 49,
//        'photo_libre_9' => 50,
    );

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
            'form' => $form->createView(),
            'carPhotos' => self::CAR_PHOTOS
        ]);
    }

    /**
     * @Route("/album/download/{id}", name="album_download", requirements={"id"="\d+"})
     */
    public function dowloadAlbum($id, UserAlbumRepository $userAlbumRepo){
        $user = $this->getUser();
        /** @var UserAlbum $userAlbum */
        $userAlbum = $userAlbumRepo->findEditableFromUser($user, $id);

        if ($userAlbum == null) {
            $this->addFlash('warning', "Vous n'êtes pas autorisé à effectuer cette action");
            return $this->redirectToRoute('album_home');
        }

        $album = $userAlbum->getAlbum();
        $mosaics = $album->getMosaics();
        $photo = $mosaics[0]->getPhoto()->getImageName();
        $photo1 = $mosaics[1]->getPhoto()->getImageName();

        $path = $this->getParameter('kernel.project_dir') . '/public/uploads/img/';
        $path1 = $this->getParameter('kernel.project_dir') . '/public/uploads/img/' . $photo1;
        //var_dump($path);die;

        $archive = new ZipArchive();
        $archive->open($path);
        $archive->addFromString("toto". '.php', "g");

        $response = new Response(file_get_contents($archive->filename));

        $d = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, "toto" . '.zip');
        $response->headers->set('Content-Disposition', $d);

        $archive->close();

        return $response;

        #return $this->file($path);

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

        var_dump($photo);die;
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
    public function removeUserAlbum(
        Request $request,
        UserAlbum $userAlbum,
        ObjectManager $manager,
        UserAlbumRepository $userAlbumRepo
    ) {
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
