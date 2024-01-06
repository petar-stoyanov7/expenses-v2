<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $user, bool $flush = false) : void
    {
        $this->getEntityManager()->persist($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function edit(User $user, bool $flush = false) : void
    {
        $this->getEntityManager()->persist($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false) : void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUsername(string $value)
    {
        try {
            return $this->createQueryBuilder('f')
                ->andWhere('f.username = :val')
                ->setParameter('val', $value)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findByEmail(string $value)
    {
        try {
            return $this->createQueryBuilder('f')
                ->andWhere('f.email = :val')
                ->setParameter('val', $value)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function getAllUsers() : array
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

    public function getByUsername(string $name) : array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.username = :val')
            ->setParameter('val', $name)
            ->getQuery()
            ->getArrayResult();
    }

    public function getByEmail(string $email) : array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.email = :val')
            ->setParameter('val', $email)
            ->getQuery()
            ->getArrayResult();
    }
}
