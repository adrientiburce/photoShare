<?php

namespace App\Repository;

use App\Entity\UserAlbum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserAlbum|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAlbum|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAlbum[]    findAll()
 * @method UserAlbum[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAlbumRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserAlbum::class);
    }

    public function findEditableFromUser($user, $id)
    {
        return $this->createQueryBuilder('userAlbum')
            ->andWhere('userAlbum.id = :id')
            ->setParameter('id', $id)
            ->andWhere('userAlbum.user = :user')
            ->setParameter('user', $user)
            ->andWhere('userAlbum.isEditable = :true')
            ->setParameter('true', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllShare($album)
    {
        return $this->createQueryBuilder('userAlbum')
            ->andWhere('userAlbum.album = :album')
            ->setParameter('album', $album)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return UserAlbum[] Returns an array of UserAlbum objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserAlbum
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
