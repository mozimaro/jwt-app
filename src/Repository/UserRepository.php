<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, User::class);
        $this->entityManager = $entityManager;
    }

    /**
     * Register a new user (persist an existing User object from factory)
     *
     * @param User $user
     * @return User
     * @throws Throwable
     */
    public function register(User $user): User
    {
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $user;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Check if a user exists by ID.
     *
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function existUserById(int $id): bool
    {
        try {
            $user = $this->find($id);
            return null !== $user;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Check if a user exists by Email.
     *
     * @param string $email
     * @return bool
     * @throws Throwable
     */
    public function existUserByEmail(string $email): bool
    {
        try {
            $user = $this->find($email);
            return null !== $user;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Get a user by email.
     *
     * @param string $email
     * @return User|null
     * @throws Throwable
     */
    public function getUserByEmail(string $email): ?User
    {
        try {
            return $this->findOneBy(['email' => $email]);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
    *
    * @return User[]
    *
    * @throws Throwable
    */
    public function getUsers(): array
    {
        try {
            $qb = $this->createQueryBuilder('u')
                       ->where('u.role = :role')
                       ->setParameter('role', 'user')
                       ->getQuery();

            return $qb->getResult();

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
