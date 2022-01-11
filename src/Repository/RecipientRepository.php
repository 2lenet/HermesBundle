<?php

namespace Lle\HermesBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Lle\HermesBundle\Entity\EmailError;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Enum\StatusEnum;

/**
 * Class RecipientRepository
 * @package Lle\HermesBundle\Repository
 *
 * @author 2LE <2le@2le.net>
 *
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
     * @param string $statusDest
     * @param string $statusMail
     * @param int $limit
     * @return Recipient[]
     */
    public function findRecipientsSending(string $statusDest, string $statusMail, int $limit): array
    {
        $qb = $this->createQueryBuilder('entity')
            ->join('entity.mail', 'mail');
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq('entity.status', ':statusDest'),
                $qb->expr()->eq('mail.status', ':statusMail')
            )
        );
        $qb->setParameters([
            'statusDest' => $statusDest,
            'statusMail' => $statusMail
        ]);
        $qb->setMaxResults($limit);
        return $qb->getQuery()->execute();
    }

    public function disableUnsubscribed(): void
    {
        $this->createQueryBuilder('e')
            ->update()
            ->set('e.status', StatusEnum::UNSUBSCRIBED)
            ->join(EmailError::class, 'ee', Join::WITH, 'ee.mail = e.toEmail')
            ->getQuery()->execute();
    }

    public function disableErrors(): void
    {
        /*$qb = $this->createQueryBuilder('e')
            ->update()
            ->set('e.status', "'error'")
            ->join(EmailError::class, 'ee', Join::WITH, 'ee.mail = e.toEmail');
        $qb->where(
            $qb->expr()->gte('ee.nbError', 3)
        );
        $qb->getQuery()->execute();*/
    }

    public function updateStatus(Mail $mail, string $status): void
    {
        $qb = $this->_em->createQueryBuilder()
            ->update(Recipient::class, 'e')
            ->set('e.status', ':status');
        $qb->where($qb->expr()->eq('e.mail', ':mail'));

        $qb->setParameters([
            'mail' => $mail,
            'status' => $status
        ]);

        $qb->getQuery()->execute();
    }

    public function countOpenRecipient(Mail $mail): int
    {
        $qb = $this->createQueryBuilder('entity')
            ->select('COUNT(entity.id)');

        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->isNotNull('entity.mail'),
                $qb->expr()->eq('entity.mail', ':mail')
            )
        );
        $qb->setParameter('mail', $mail);

        return $qb->getQuery()->getSingleScalarResult();
    }
}
