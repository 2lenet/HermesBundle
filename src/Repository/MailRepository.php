<?php

namespace Lle\HermesBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Enum\StatusEnum;

/**
 * Class MailRepository
 * @package Lle\HermesBundle\Repository
 *
 * @author 2LE <2le@2le.net>
 *
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
        $copyMail->setStatus(StatusEnum::DRAFT);
        $copyMail->setSendingDate(null);
        $copyMail->setTotalSended(0);
        $copyMail->setTotalOpened(0);
        $copyMail->setTotalUnsubscribed(0);
        $copyMail->setTotalError(0);

        foreach ($mail->getRecipients() as $recipient) {
            $cloneRecipient = clone $recipient;
            $cloneRecipient->setStatus(StatusEnum::DRAFT);
            $cloneRecipient->setDateOuverture(null);
            $copyMail->addRecipient($cloneRecipient);
        }

        return $copyMail;
    }

    public function updateTotalOpened(Mail $mail, int $countDestinatairesOpen): void
    {
        $qb = $this->_em->createQueryBuilder()
            ->update(Mail::class, 'entity')
            ->set('entity.totalOpened', $countDestinatairesOpen)
            ->where('entity.id = :mail')
            ->setParameter('mail', $mail->getId());

        $qb->getQuery()->execute();
    }
}
