<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Crudit\Config;

use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\HermesBundle\Crudit\Datasource\MailDatasource;

class MailCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        MailDatasource $datasource
    ) {
        $this->datasource = $datasource;
    }

    /**
     * @param string $key
     * @return Field[]
     */
    public function getFields($key): array
    {
        $subject = Field::new('subject')
            ->setTemplate('@LleHermes/crud/_subject.html.twig');
        $recipients = Field::new('recipients')
            ->setTemplate('@LleHermes/crud/_recipient.html.twig');
        $sendingDate = Field::new('sendingDate');
        $status = Field::new('status');
        $html = Field::new('html')
            ->setTemplate('@LleHermes/crud/_html.html.twig')
            ->setCssClass('col-12');
        $attachement = Field::new('jsonAttachement')
            ->setTemplate('@LleHermes/crud/_attachement.html.twig');

        $fields = [];

        switch ($key) {
            case CrudConfigInterface::SHOW:
                $fields = [
                    $subject,
                    $recipients,
                    $sendingDate,
                    $status,
                    $html,
                    $attachement,
                ];
                break;
            default:
                $fields = [
                    $subject,
                    $recipients,
                    $sendingDate,
                    $status,
                ];
        }

        return $fields;
    }

    public function getRootRoute(): string
    {
        return 'lle_hermes_crudit_mail';
    }
}
