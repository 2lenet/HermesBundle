<?php

namespace Lle\HermesBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\HermesBundle\Entity\Consent;

/**
 * @method Consent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Consent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Consent[]    findAll()
 * @method Consent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consent::class);
    }

    public function findOneByEmailAndType(string $email, string $type): ?Consent
    {
        return $this->findOneBy(['email' => $email, 'type' => $type]);
    }
}
