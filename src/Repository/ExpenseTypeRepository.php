<?php

namespace App\Repository;

use App\Entity\ExpenseType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExpenseType>
 *
 * @method ExpenseType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpenseType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpenseType[]    findAll()
 * @method ExpenseType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpenseType::class);
    }

    public function add(ExpenseType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ExpenseType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByName(string $name)
    {
        try {
            return $this->createQueryBuilder('f')
                ->andWhere('f.name = :val')
                ->setParameter('val', $name)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return [];
        }
    }
}
