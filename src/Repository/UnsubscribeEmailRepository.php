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
        return $this->createQueryBuilder('unsubscribeEmail')
            ->select('unsubscribeEmail.email as email', 'IDENTITY(unsubscribeEmail.typeTemplate) as typeTemplateId')
            ->getQuery()
            ->getScalarResult();
    }

    public function findGlobalByEmail(string $email): ?UnsubscribeEmail
    {
        return $this->createQueryBuilder('unsubscribeEmail')
            ->andWhere('unsubscribeEmail.email = :email')
            ->andWhere('unsubscribeEmail.typeTemplate IS NULL')
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByTypeForEmail(string $email): array
    {
        /** @var UnsubscribeEmail[] $unsubscriptions */
        $unsubscriptions = $this->createQueryBuilder('unsubscribeEmail')
            ->andWhere('unsubscribeEmail.email = :email')
            ->andWhere('unsubscribeEmail.typeTemplate IS NOT NULL')
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($unsubscriptions as $unsubscription) {
            $indexed[(int)$unsubscription->getTypeTemplate()?->getId()] = $unsubscription;
        }

        return $indexed;
    }
}
