<?php

namespace App\Tests\Entity;

use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Entity\TypeTemplate;
use PHPUnit\Framework\TestCase;

/**
 * Class TypeTemplateTest
 * @package phpunit\Unit\Entity
 *
 * @author 2LE <2le@2le.net>
 */
class TypeTemplateTest extends TestCase
{
    public function testCreate(): void
    {
        $typeTemplate = new TypeTemplate();

        $typeTemplate->setCode('technical');
        self::assertEquals('technical', $typeTemplate->getCode());

        $typeTemplate->setLabel('Technique');
        self::assertEquals('Technique', $typeTemplate->getLabel());
        self::assertEquals('Technique', (string)$typeTemplate);

        self::assertTrue($typeTemplate->isUnsubscriptionsAllowed());
        $typeTemplate->setUnsubscriptionsAllowed(false);
        self::assertFalse($typeTemplate->isUnsubscriptionsAllowed());

        self::assertCount(0, $typeTemplate->getTemplates());
    }

    public function testCanDelete(): void
    {
        $typeTemplate = new TypeTemplate();
        self::assertTrue($typeTemplate->canDelete());

        $typeTemplate->getTemplates()->add((new Template())->setTypeTemplate($typeTemplate));
        self::assertEquals('crud.canDelete.type_template', $typeTemplate->canDelete());
    }
}
