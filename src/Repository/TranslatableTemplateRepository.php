<?php

namespace Lle\HermesBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\HermesBundle\Contracts\TemplateInterface;
use Lle\HermesBundle\Contracts\TemplateRepositoryInterface;
use Lle\HermesBundle\Translatable\TranslatableTemplate;

/**
 * @method TranslatableTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method TranslatableTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method TranslatableTemplate[]    findAll()
 * @method TranslatableTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TranslatableTemplateRepository extends ServiceEntityRepository implements TemplateRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranslatableTemplate::class);
    }

    public function duplicateTemplate(TemplateInterface $template, string $code): TemplateInterface
    {
        $copyTemplate = clone $template;
        $copyTemplate->setLibelle('Copy of ' . $template->getLibelle());
        $copyTemplate->setCode($code);

        return $copyTemplate;
    }
}
