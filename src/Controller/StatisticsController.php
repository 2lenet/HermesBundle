<?php

namespace Lle\HermesBundle\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Link;
use Lle\HermesBundle\Entity\LinkOpening;
use Lle\HermesBundle\Entity\Recipient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class StatisticsController extends AbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/statistics/{recipient}/{link}', name: 'statistics', methods: ['GET'])]
    public function statistics(Request $request, ?Recipient $recipient = null, ?Link $link = null): RedirectResponse
    {
        $fallback = $request->query->get('fallback');

        // If the recipient or the link doesn't exists anymore :
        // redirect to the fallback URL if provided, otherwise throw a 404
        if (!$recipient || !$link) {
            if (!$fallback) {
                throw $this->createNotFoundException();
            }

            return new RedirectResponse((string)$fallback);
        }

        $linkOpeningRepository = $this->em->getRepository(LinkOpening::class);
        $linkOpening = $linkOpeningRepository->findByLinkAndRecipient($link, $recipient);

        if ($linkOpening === null) {
            $linkOpening = new LinkOpening();
            $linkOpening->setRecipient($recipient);
            $linkOpening->setLink($link);
            $linkOpening->setNbOpenings(1);
            $linkOpening->setCreatedAt(new DateTime());
        } else {
            $nbOpenings = $linkOpening->getNbOpenings() + 1;
            $linkOpening->setNbOpenings($nbOpenings);
            $linkOpening->setUpdatedAt(new DateTime());
        }

        $this->em->persist($linkOpening);
        $this->em->flush();

        return new RedirectResponse((string)$link->getUrl());
    }
}
