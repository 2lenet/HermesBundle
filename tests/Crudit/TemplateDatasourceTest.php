<?php

namespace App\Tests\Crudit;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CruditBundle\Filter\FilterState;
use Lle\HermesBundle\Contracts\TemplateInterface;
use Lle\HermesBundle\Crudit\Datasource\TemplateDatasource;
use Lle\HermesBundle\Entity\Template;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TemplateDatasourceTest extends TestCase
{
    private function newTemplate(?string $type): TemplateInterface
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request(null === $type ? [] : ['type' => $type]));

        $datasource = new TemplateDatasource(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(FilterState::class),
            Template::class,
        );
        $datasource->setRequestStack($requestStack);

        $template = $datasource->newInstance();
        self::assertInstanceOf(TemplateInterface::class, $template);

        return $template;
    }

    /**
     * @return iterable<string, array{string|null, string}>
     */
    public static function typeProvider(): iterable
    {
        yield 'html' => [TemplateInterface::TYPE_HTML, TemplateInterface::TYPE_HTML];
        yield 'ckeditor' => [TemplateInterface::TYPE_CKEDITOR, TemplateInterface::TYPE_CKEDITOR];
        yield 'mjml' => [TemplateInterface::TYPE_MJML, TemplateInterface::TYPE_MJML];
        yield 'unknown type' => ['unknown', TemplateInterface::TYPE_HTML];
        yield 'no type' => [null, TemplateInterface::TYPE_HTML];
    }

    #[DataProvider('typeProvider')]
    public function testTheNewTemplateTakesTheTypeOfTheRequest(?string $requestType, string $expectedType): void
    {
        self::assertSame($expectedType, $this->newTemplate($requestType)->getType());
    }
}
