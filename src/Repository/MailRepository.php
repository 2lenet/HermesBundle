<?php

namespace Lle\HermesBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Lle\HermesBundle\Entity\Mail;

/**
 * @method Mail|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mail|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mail[]    findAll()
 * @method Mail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Mail[]    findByStatus(string $status)
 */
class MailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mail::class);
    }

    public function getDashboardMails(int $page = 1, int $number = 30, ?int $tenantId = null): Paginator
    {
        $qb = $this->createQueryBuilder("m")
            ->orderBy("m.id", "DESC");

        if ($tenantId) {
            $qb->andWhere('m.tenantId = :id')
                ->setParameter('id', $tenantId);
        }

        $paginator = new Paginator($qb);
        $paginator
            ->getQuery()
            ->setFirstResult($number * ($page - 1))
            ->setMaxResults($number);

        return $paginator;
    }

    public function findOldMails(\DateTime $date): array
    {
        return $this->createQueryBuilder("m")
            ->orWhere("m.sendingDate IS NOT NULL AND m.sendingDate < :date")
            ->orWhere("m.status LIKE :status AND m.createdAt < :date")
            ->andWhere("m.attachmentsDeleted = false")
            ->setParameter("date", $date)
            ->setParameter("status", Mail::STATUS_ERROR)
            ->getQuery()
            ->getResult();
    }

    /**
     * Reads the content of the mails in the given statuses, by batches of ids (keyset pagination):
     * no entity is hydrated and only one batch is held in memory, whatever the size of the table.
     *
     * @param string[] $statuses
     *
     * @return array<int, array{id: int, subject: ?string, text: ?string, html: ?string}>
     */
    public function findContentsByStatus(array $statuses, int $afterId, int $limit): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.id', 'm.subject', 'm.text', 'm.html')
            ->andWhere('m.status IN (:statuses)')
            ->andWhere('m.id > :afterId')
            ->setParameter('statuses', $statuses)
            ->setParameter('afterId', $afterId)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }
}
