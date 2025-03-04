<?php

namespace App\Tests\Service;

use Lle\HermesBundle\Dto\Api\ListTemplateRequest;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Repository\TemplateRepository;
use Lle\HermesBundle\Service\Api\ListTemplateService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ListTemplateServiceTest extends TestCase
{
    private NormalizerInterface $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = $this->createMock(NormalizerInterface::class);
    }

    public function testList(): void
    {
        $templateRepository = $this->createMock(TemplateRepository::class);
        $templateRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([$this->createTemplate('label', 'subject')]);
        $templateRepository
            ->expects(self::never())
            ->method('findBy');

        $service = new ListTemplateService($this->normalizer, $templateRepository);

        $listTemplateRequest = new ListTemplateRequest();
        $listTemplateRequest->tenantId = null;

        $service->list($listTemplateRequest);
    }

    public function testListWithTenant(): void
    {
        $tenantId = 1;

        $templateRepository = $this->createMock(TemplateRepository::class);
        $templateRepository
            ->expects(self::once())
            ->method('findBy')
            ->willReturn([$this->createTemplate('label2', 'subject2', $tenantId)]);
        $templateRepository
            ->expects(self::never())
            ->method('findAll');

        $service = new ListTemplateService($this->normalizer, $templateRepository);

        $listTemplateRequest = new ListTemplateRequest();
        $listTemplateRequest->tenantId = $tenantId;

        $service->list($listTemplateRequest);
    }

    protected function createTemplate(string $label, string $subject, ?int $tenantId = null): Template
    {
        $template = new Template();
        $template
            ->setLibelle($label)
            ->setSubject($subject)
            ->setSenderEmail('no-reply@email.com')
            ->setText('text')
            ->setCode('code')
            ->setHtml('html')
            ->setUnsubscriptions(true)
            ->setStatistics(true);

        if ($tenantId) {
            $template->setTenantId($tenantId);
        }

        return $template;
    }
}
