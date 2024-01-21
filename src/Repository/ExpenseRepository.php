<?php

namespace App\Repository;

use App\Entity\Expense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends ServiceEntityRepository<Expense>
 *
 * @method Expense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expense[]    findAll()
 * @method Expense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function add(Expense $entity, bool $flush = false) : void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Expense $entity, bool $flush = false) : void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function edit(Expense $expense, bool $flush = false) : void
    {
        $this->getEntityManager()->persist($expense);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function deleteByCarId(int $carId, bool $flush = false) : void
    {
        $this->createQueryBuilder('e')
            ->delete()
            ->andWhere('e.car = :carId')
            ->setParameter('carId', $carId)
            ->getQuery()
            ->execute();

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getExpenses($parameters)
    {
        $query = $this->createQueryBuilder('e')
            ->select([
                'e.id',
                'c.id as car_id',
                "CONCAT(c.brand,' ',c.model) as car",
                'e.mileage',
                "CASE WHEN (et.displayName IS NOT NULL) THEN et.displayName ELSE et.name END AS expense",
                "CASE WHEN (ft.displayName IS NOT NULL) THEN ft.displayName ELSE ft.name END AS fuel",
                'e.liters',
                'e.value',
                'e.notes',
                'e.createdAt',
                'e.updatedAt',
            ])
            ->leftJoin(
                'App\Entity\ExpenseType',
                'et',
                Join::WITH,
                'e.expenseType = et.id'
            )
            ->leftJoin(
                'App\Entity\FuelType',
                'ft',
                Join::WITH,
                'e.fuelType = ft.id'
            )
            ->leftJoin(
                'App\Entity\Car',
                'c',
                Join::WITH,
                'e.car = c.id'
            );

        if (isset($parameters['from'])) {
            $query->andWhere('e.createdAt > :from')
                ->setParameter('from', $parameters['from']);
            unset($parameters['from']);
        }
        if (isset($parameters['to'])) {
            $query->andWhere('e.createdAt < :to')
                ->setParameter('to', $parameters['to']);
            unset($parameters['to']);
        }

        if (!empty($parameters)) {
            foreach ($parameters as $index => $value) {
                $query->andWhere("e.{$index} = :{$index}");
                $query->setParameter($index, $value);
            }
        }

        return $query
            ->getQuery()
            ->getArrayResult();
    }
}
