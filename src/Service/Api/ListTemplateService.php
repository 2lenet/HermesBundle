<?php

namespace Lle\HermesBundle\Service\Api;

use Lle\HermesBundle\Dto\Api\ListTemplateRequest;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Repository\TemplateRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ListTemplateService extends AbstractApiService
{
    public function __construct(
        NormalizerInterface $normalizer,
        protected TemplateRepository $templateRepository,
    ) {
        parent::__construct($normalizer);
    }

    public function list(ListTemplateRequest $listTemplateRequest): array
    {
        if ($listTemplateRequest->tenantId) {
            $templates = $this->templateRepository->findBy([
                'tenantId' => $listTemplateRequest->tenantId,
            ]);
        } else {
            $templates = $this->templateRepository->findAll();
        }

        return $this->normalize($templates, [Template::TEMPLATE_API_GROUP]);
    }
}
