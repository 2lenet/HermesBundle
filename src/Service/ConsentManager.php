<?php

namespace Lle\HermesBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Consent;
use Lle\HermesBundle\Repository\ConsentRepository;

/**
 * Class ConsentManager
 * @package Lle\HermesBundle\Service
 *
 * @author 2LE <2le@2le.net>
 */
class ConsentManager
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ConsentRepository $consentRepository,
    ) {
    }

    public function hasConsent(string $email, string $type): bool
    {
        $consent = $this->consentRepository->findOneByEmailAndType($email, $type);

        return !$consent || $consent->isValue();
    }

    public function setConsent(string $email, string $type, bool $value): Consent
    {
        $consent = $this->consentRepository->findOneByEmailAndType($email, $type);

        if (!$consent) {
            $consent = new Consent();
            $consent->setEmail($email);
            $consent->setType($type);
            $this->em->persist($consent);
        }

        $consent->setValue($value);
        $consent->setDatetime(new DateTime());
        $this->em->flush();

        return $consent;
    }
}
