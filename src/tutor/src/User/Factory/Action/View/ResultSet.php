<?php

namespace Tutor\User\Factory\Action\View;

use Psr\Container\ContainerInterface;
use User;
use Zend\Expressive\Template;
use Course;
use Tutor;

class ResultSet
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $template = $container->get(Template\TemplateRendererInterface::class);

        /**
         * @var Course\Model\CourseTable $courseTable
         */
        $courseTable = $container->get(Course\Model\CourseTable::class);

        return new User\Action\View\ResultSet($template, $courseTable);
    }
}
