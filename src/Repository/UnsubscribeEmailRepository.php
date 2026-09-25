<?php

namespace Lle\HermesBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\HermesBundle\Entity\UnsubscribeEmail;

/**
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
     * @return array<array{email: string, typeTemplateId: int|null}>
     */
    public function findEmailsUnsubscribed(): array
    {
        return $this->createQueryBuilder('entity')
            ->select('entity.email as email', 'IDENTITY(entity.typeTemplate) as typeTemplateId')
            ->getQuery()
            ->getScalarResult();
    }

    public function findGlobalByEmail(string $email): ?UnsubscribeEmail
    {
        return $this->createQueryBuilder('entity')
            ->andWhere('LOWER(entity.email) = :email')
            ->andWhere('entity.typeTemplate IS NULL')
            ->setParameter('email', strtolower($email))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByTypeForEmail(string $email): array
    {
        /** @var UnsubscribeEmail[] $unsubscriptions */
        $unsubscriptions = $this->createQueryBuilder('entity')
            ->andWhere('LOWER(entity.email) = :email')
            ->andWhere('entity.typeTemplate IS NOT NULL')
            ->setParameter('email', strtolower($email))
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($unsubscriptions as $unsubscription) {
            $indexed[(int)$unsubscription->getTypeTemplate()?->getId()] = $unsubscription;
        }

        return $indexed;
    }
}
