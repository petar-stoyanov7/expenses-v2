<?php

namespace App\Repository;

use App\Entity\FuelType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FuelType>
 *
 * @method FuelType|null find($id, $lockMode = null, $lockVersion = null)
 * @method FuelType|null findOneBy(array $criteria, array $orderBy = null)
 * @method FuelType[]    findAll()
 * @method FuelType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FuelTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FuelType::class);
    }

    public function add(FuelType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FuelType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return FuelType[] Returns an array of FuelType objects
     */
    public function findByFuelName(string $value): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.name = :val')
            ->setParameter('val', $value)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getAllFuels() : array
    {
        return $this->createQueryBuilder('q')
            ->getQuery()
            ->getArrayResult();
    }

    public function getById(int $id) : array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getArrayResult();
    }

    public function getByName(string $name)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.name = :val')
            ->setParameter('val', $name)
            ->getQuery()
            ->getArrayResult();
    }

//    /**
//     * @return FuelType[] Returns an array of FuelType objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FuelType
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
