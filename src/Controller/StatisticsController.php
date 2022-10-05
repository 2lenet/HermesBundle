<?php

namespace Lle\HermesBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Link;
use Lle\HermesBundle\Entity\LinkOpening;
use Lle\HermesBundle\Entity\Recipient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class StatisticsController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/statistics/{recipient}/{link}", name="statistics")
     */
    public function statistics(Recipient $recipient, Link $link): RedirectResponse
    {
        $linkOpeningRepository = $this->em->getRepository(LinkOpening::class);
        $linkOpening = $linkOpeningRepository->findByLinkAndRecipient($link, $recipient);

        if ($linkOpening === null) {
            $linkOpening = new LinkOpening();
            $linkOpening->setRecipient($recipient);
            $linkOpening->setLink($link);
            $linkOpening->setNbOpenings(1);
        } else {
            $nbOpenings = $linkOpening->getNbOpenings() + 1;
            $linkOpening->setNbOpenings($nbOpenings);
        }

        $this->em->persist($linkOpening);
        $this->em->flush();

        return new RedirectResponse($link->getUrl());
    }
}