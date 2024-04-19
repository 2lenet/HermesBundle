<?php

namespace Lle\HermesBundle\Controller;

use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Service\MailTracker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

class MailTrackerController extends AbstractController
{
    public function __construct(
        protected readonly KernelInterface $kernel,
        protected readonly MailTracker $mailTracker,
    ) {
    }

    #[Route('/mailOpened/{recipient}', name: 'mail_opened', methods: ['GET'])]
    public function mailOpened(Recipient $recipient): BinaryFileResponse
    {
        $this->mailTracker->updateTotalOpened($recipient);

        $image = $this->kernel->getProjectDir() . '/vendor/2lenet/hermes-bundle/assets/img/pixel.png';
        $headers = ['Content-Type' => 'image/png'];

        return new BinaryFileResponse($image, Response::HTTP_OK, $headers);
    }
}
