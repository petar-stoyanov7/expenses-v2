<?php

namespace App\Repository;

use App\Entity\Car;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Car>
 *
 * @method Car|null find($id, $lockMode = null, $lockVersion = null)
 * @method Car|null findOneBy(array $criteria, array $orderBy = null)
 * @method Car[]    findAll()
 * @method Car[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    public function add(Car $car, bool $flush = false): void
    {
        $this->getEntityManager()->persist($car);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function edit(Car $car, bool $flush = false): void
    {
        $this->getEntityManager()->persist($car);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Car $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUserId(int $userId)
    {
        try {
            return $this->createQueryBuilder('f')
                ->andWhere('f.user_id = :val')
                ->setParameter('val', $userId)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function getAllCars() : array
    {
        return $this->createQueryBuilder('q')
            ->getQuery()
            ->getArrayResult();
    }

    public function getByCarId(int $carId) : array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.id = :val')
            ->setParameter('val', $carId)
            ->getQuery()
            ->getArrayResult();
    }
    public function getByUserId(int $userId) : array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.user = :val')
            ->setParameter('val', $userId)
            ->getQuery()
            ->getArrayResult();
    }
}
