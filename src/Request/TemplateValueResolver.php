<?php

namespace Lle\HermesBundle\Request;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Contracts\TemplateInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class TemplateValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        #[Autowire(param: 'lle_hermes.template_class')]
        private readonly string $templateClass,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== TemplateInterface::class) {
            return [];
        }

        $id = $request->attributes->get($argument->getName()) ?? $request->attributes->get('id');
        if ($id === null) {
            return [];
        }

        $entity = $this->em->getRepository($this->templateClass)->find($id);
        if ($entity === null) {
            return [];
        }

        return [$entity];
    }
}
