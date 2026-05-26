<?php

namespace Lle\HermesBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;

/**
 * @method Recipient|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipient|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipient[]    findAll()
 * @method Recipient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipient::class);
    }

    /**
     * @return Recipient[]
     */
    public function findRecipientsSending(string $recipientStatus, string $mailStatus, int $limit): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('r')
            ->leftJoin('r.mail', 'm')
            ->leftJoin('r.ccMail', 'cc')
            ->andWhere(
                '(m.sendAtDate IS NULL OR m.sendAtDate < :now)
                 AND (m.status = :status_m OR cc.status = :status_m)
                 AND (
                    r.status = :status_r
                    OR (r.status = :status_retry AND r.retryAt <= :now)
                 )'
            )
            ->setParameter('status_r', $recipientStatus)
            ->setParameter('status_retry', Recipient::STATUS_RETRY)
            ->setParameter('status_m', $mailStatus)
            ->setParameter('now', $now)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countOpenRecipients(Mail $mail): int
    {
        $result = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.mail = :mail')
            ->andWhere('r.openDate IS NOT NULL')
            ->setParameter('mail', $mail)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$result;
    }
}
