<?php

namespace Lle\HermesBundle\Controller\Api\V1;

use Lle\HermesBundle\Dto\Api\ListTemplateRequest;
use Lle\HermesBundle\Service\Api\ListTemplateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/template')]
class TemplateController extends AbstractController
{
    public function __construct(
        protected ListTemplateService $listTemplateService,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(#[MapRequestPayload] ListTemplateRequest $listTemplateRequest): JsonResponse
    {
        $templates = $this->listTemplateService->list($listTemplateRequest);

        return new JsonResponse($templates);
    }
}
