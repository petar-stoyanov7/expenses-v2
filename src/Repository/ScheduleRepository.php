<?php

namespace App\Repository;

use App\Entity\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends ServiceEntityRepository<Schedule>
 *
 * @method Schedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Schedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Schedule[]    findAll()
 * @method Schedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    public function add(Schedule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Schedule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function edit(Schedule $schedule, bool $flush = false): void
    {
        $this->getEntityManager()->persist($schedule);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function deleteByCarId(int $carId, bool $flush = false): void
    {
        $this->createQueryBuilder('s')
            ->delete()
            ->andWhere('s.car = :carId')
            ->setParameter('carId', $carId)
            ->getQuery()
            ->execute();

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getCarSchedules(int $carId, array $parameters)
    {
        $query = $this->createQueryBuilder('s')
            ->select([
                's.id',
                'c.id as carId',
                "CONCAT(c.brand,' ',c.model, ' ', c.year) as car",
                's.name',
                's.mileage',
                's.date',
                's.notes'
            ])
            ->leftJoin(
                'App\Entity\Car',
                'c',
                Join::WITH,
                's.car = c.id'
            )
            ->andWhere('s.car = :car');
        $boundParameters = ['car' => $carId];

        if (!empty($parameters['mileage']) && !empty($parameters['date'])) {
            $query->andWhere('s.mileage <= :mileage OR s.date <= :date');
            $boundParameters['mileage'] = $parameters['mileage'];
            $boundParameters['date'] = $parameters['date'];
        } elseif (!empty($parameters['mileage'])) {
            $query->andWhere('s.mileage <= :mileage');
            $boundParameters['mileage'] = $parameters['mileage'];
        } elseif (!empty($parameters['date'])) {
            $query->andWhere('s.date <= :date');
            $boundParameters['date'] = $parameters['date'];
        }

        $query->setParameters($boundParameters);

        return $query
            ->getQuery()
            ->getArrayResult();
    }
}
