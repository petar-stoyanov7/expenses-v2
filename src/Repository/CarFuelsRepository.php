<?php

namespace App\Repository;

use App\Entity\CarFuels;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CarFuels>
 *
 * @method CarFuels|null find($id, $lockMode = null, $lockVersion = null)
 * @method CarFuels|null findOneBy(array $criteria, array $orderBy = null)
 * @method CarFuels[]    findAll()
 * @method CarFuels[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarFuelsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarFuels::class);
    }

    public function add(CarFuels $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CarFuels $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function getByCarIdAndFuelId(int $carId, int $fuelId)
    {
        try {
            return $this->createQueryBuilder('f')
                ->andWhere('f.car = :car')
                ->andWhere('f.fuel = :fuel')
                ->setParameters([
                    'car' => $carId,
                    'fuel' => $fuelId
                ])
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function getCarFuels(int $carId)
    {
        return $this->createQueryBuilder('cf')
            ->select('ft')
            ->leftJoin(
                'App\Entity\FuelType',
                'ft',
                Join::WITH,
                'cf.fuel = ft.id'
            )
            ->where('cf.car = :car_id')
            ->setParameter('car_id', $carId)
            ->getQuery()
            ->getArrayResult();
    }
}
