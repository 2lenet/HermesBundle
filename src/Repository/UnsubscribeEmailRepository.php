<?php

namespace Lle\HermesBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\HermesBundle\Entity\UnsubscribeEmail;

/**
 * Class UnsubscribeEmailRepository
 * @package Lle\HermesBundle\Repository
 *
 * @author 2LE <2le@2le.net>
 *
 * @method UnsubscribeEmail|null find($id, $lockMode = null, $lockVersion = null)
 * @method UnsubscribeEmail|null findOneBy(array $criteria, array $orderBy = null)
 * @method UnsubscribeEmail[]    findAll()
 * @method UnsubscribeEmail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnsubscribeEmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UnsubscribeEmail::class);
    }

    /**
     * @return string[]
     */
    public function findEmailsUnsubscribed(): array
    {
        return $this->createQueryBuilder('entity')
            ->select('entity.email as email')
            ->getQuery()
            ->getScalarResult();
    }
}
