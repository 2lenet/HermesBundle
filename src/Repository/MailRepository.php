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

    public function copyMail(Mail $mail): Mail
    {
        $copyMail = clone $mail;
        $copyMail->setStatus(Mail::STATUS_DRAFT);
        $copyMail->setSendingDate(null);
        $copyMail->setTotalSended(0);
        $copyMail->setTotalOpened(0);
        $copyMail->setTotalUnsubscribed(0);
        $copyMail->setTotalError(0);

        foreach ($mail->getRecipients() as $recipient) {
            $cloneRecipient = clone $recipient;
            $cloneRecipient->setStatus(Mail::STATUS_DRAFT);
            $cloneRecipient->setDateOuverture(null);
            $copyMail->addRecipient($cloneRecipient);
        }

        return $copyMail;
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
}
