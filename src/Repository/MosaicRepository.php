<?php

namespace App\Repository;

use App\Entity\Mosaic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Mosaic|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mosaic|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mosaic[]    findAll()
 * @method Mosaic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MosaicRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Mosaic::class);
    }

    // /**
    //  * @return Mosaic[] Returns an array of Mosaic objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Mosaic
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
