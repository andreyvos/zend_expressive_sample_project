<?php
namespace Student\User\Factory\Action\View\ResultSet;
use User;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template;

class ByUserRole
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $template = $container->get(Template\TemplateRendererInterface::class);
        return new User\Action\View\ResultSet\ByUserRole($template);
    }
}