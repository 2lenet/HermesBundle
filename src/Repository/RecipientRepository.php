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
        return $this->createQueryBuilder('r')
            ->leftJoin('r.mail', 'm')
            ->leftJoin('r.ccMail', 'cc')
            ->andWhere(
                '(m.sendAtDate IS NULL OR m.sendAtDate < :now) AND r.status = :status_r AND m.status = :status_m'
            )
            ->orWhere(
                '(m.sendAtDate IS NULL OR m.sendAtDate < :now) AND r.status = :status_r AND cc.status = :status_m'
            )
            ->setParameter('status_r', $recipientStatus)
            ->setParameter('status_m', $mailStatus)
            ->setParameter('now', new \DateTime())
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

    public function countStatusesByMail(Mail $mail): array
    {
        return $this->createQueryBuilder('r', 'r.status')
            ->select('r.status AS status, COUNT(r.id) AS count')
            ->where('r.mail = :mail')
            ->andWhere('r.test = :test')
            ->setParameter('mail', $mail)
            ->setParameter('test', false)
            ->groupBy('r.status')
            ->getQuery()
            ->getArrayResult();
    }
}
