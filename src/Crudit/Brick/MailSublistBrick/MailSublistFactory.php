<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Brick\MailSublistBrick;

use Lle\CruditBundle\Brick\SublistBrick\SublistFactory;
use Lle\CruditBundle\Contracts\BrickConfigInterface;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Datasource\DatasourceFilter;
use Lle\CruditBundle\Datasource\DatasourceParams;
use Lle\CruditBundle\Dto\BrickView;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Dto\ResourceView;
use Lle\CruditBundle\Resolver\ResourceResolver;
use Lle\HermesBundle\Crudit\Config\MailCrudConfig;
use Lle\HermesBundle\Crudit\Datasource\MailDatasource;
use Symfony\Component\HttpFoundation\RequestStack;

class MailSublistFactory extends SublistFactory
{
    protected DatasourceParams $datasourceParams;

    public function __construct(
        ResourceResolver $resourceResolver,
        RequestStack $requestStack,
        protected MailCrudConfig $mailCrudConfig,
        protected MailDatasource $mailDatasource,
    ) {
        parent::__construct($resourceResolver, $requestStack);

        $this->datasourceParams = new DatasourceParams();
    }

    public function support(BrickConfigInterface $brickConfigurator): bool
    {
        return (MailSublistConfig::class === get_class($brickConfigurator));
    }

    public function buildView(BrickConfigInterface $brickConfigurator): BrickView
    {
        $view = new BrickView($brickConfigurator);
        if ($brickConfigurator instanceof MailSublistConfig) {
            $view
                ->setTemplate($brickConfigurator->getTemplate() ?? '@LleCrudit/brick/sublist_items')
                ->setConfig($this->getConfig($brickConfigurator))
                ->setPath($this->getPath($brickConfigurator))
                ->setData([
                    'lines' => $this->getLines($brickConfigurator),
                ]);
        }

        return $view;
    }

    public function getConfig(MailSublistConfig $brickConfigurator): array
    {
        return [
            'fields' => $this->mailCrudConfig->getFields(CrudConfigInterface::INDEX),
            'actions' => $this->mailCrudConfig->getItemActions(),
            'batch_actions' => [],
            'name' => $this->mailCrudConfig->getName(),
            'title' => $brickConfigurator->getTitle(),
            'titleCss' => $brickConfigurator->getTitleCss(),
            'datasource_params' => $this->datasourceParams,
            'detail' => null,
            'hidden_action' => false,
            'bulk' => false,
            'sort' => ['name' => 'id', 'direction' => 'ASC'],
            'canModifyNbEntityPerPage' => false,
            'choices_nb_items' => $this->mailCrudConfig->getChoicesNbItems(),
            'translation_domain' => $this->mailCrudConfig->getTranslationDomain(),
        ];
    }

    /** @return ResourceView[] */
    private function getLines(MailSublistConfig $brickConfigurator): array
    {
        $lines = [];
        $foreignKeyValue = $this->getRequest()->attributes->get('id');
        $resource = $brickConfigurator->getDataSource()->get($foreignKeyValue);
        if (!$resource) {
            return $lines;
        }

        $this->datasourceParams->setEnableFilters(false);
        $this->datasourceParams->setFilters(array_merge($this->datasourceParams->getFilters(), [
            new DatasourceFilter('entityClass', get_class($resource)),
            new DatasourceFilter('entityId', $foreignKeyValue),
        ]));
        $this->datasourceParams->setCount($this->mailDatasource->count($this->datasourceParams));
        $resources = $this->mailDatasource->list($this->datasourceParams);

        foreach ($resources as $resource) {
            $lines[] = $this->resourceResolver->resolve(
                $resource,
                $this->mailCrudConfig->getFields(CrudConfigInterface::INDEX),
                $brickConfigurator->getDatasource(),
                $this->mailCrudConfig,
            );
        }

        return $lines;
    }
}
