<?php

namespace Lle\HermesBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\HermesBundle\Entity\Link;
use Lle\HermesBundle\Entity\LinkOpening;
use Lle\HermesBundle\Entity\Recipient;

/**
 * @method LinkOpening|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinkOpening|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinkOpening[]    findAll()
 * @method LinkOpening[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkOpeningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinkOpening::class);
    }

    public function findByLinkAndRecipient(Link $link, Recipient $recipient): ?LinkOpening
    {
        return $this->createQueryBuilder('linkOpening')
            ->where('linkOpening.link = :link')
            ->andWhere('linkOpening.recipient = :recipient')
            ->setParameter('link', $link)
            ->setParameter('recipient', $recipient)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
