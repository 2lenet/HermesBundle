<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Action\EditAction;
use Lle\CruditBundle\Dto\Action\ItemAction;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Dto\Path;
use Lle\HermesBundle\Crudit\Datasource\PersonalizedTemplateDatasource;
use Lle\HermesBundle\Crudit\Datasource\TemplateDatasource;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PersonalizedTemplateCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        PersonalizedTemplateDatasource $datasource,
        private ParameterBagInterface $parameterBag,
    ) {
        $this->datasource = $datasource;
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $subject = Field::new('subject');
        $libelle = Field::new('libelle');
        $senderEmail = Field::new('senderEmail');
        $senderName = Field::new('senderName');
        $code = Field::new('code');
        $text = Field::new('text');
        $unsubscriptions = Field::new('unsubscriptions');
        $html = Field::new('html')
            ->setTemplate('@LleHermes/crud/_template_html.html.twig')
            ->setCssClass('col-12');
        $statistics = Field::new('statistics');

        if ($key == CrudConfigInterface::INDEX) {
            return [
                $libelle,
                $subject,
                $senderName,
                $senderEmail,
                $code,
                $unsubscriptions,
                $statistics,
            ];
        }

        return [
            $libelle,
            $subject,
            $senderName,
            $senderEmail,
            $text,
            $code,
            $html,
            $unsubscriptions,
            $statistics,
        ];
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_personalizedtemplate';
    }

    public function getListActions(): array
    {
        return [];
    }
}
