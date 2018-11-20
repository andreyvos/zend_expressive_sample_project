<?php

namespace Credit\Factory\Action\Form;

use Psr\Container\ContainerInterface;
use Credit\Form;
use Credit\Action;
use User\Model;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template;
use Zend\Diactoros\Response\RedirectResponse;
use Credit;

class Student
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $template = $container->get(Template\TemplateRendererInterface::class);
        $userTable = $container->get(Model\UserTable::class);
		$creditStudentTable = $container->get(Credit\Model\CreditStudentTable::class);
		$recruitTable = $container->get(Credit\Model\RecruitTable::class);

        /**
         * @var UrlHelper $urlHelper
         */
        $urlHelper = $container->get(UrlHelper::class);
		$selectCourse = $container->get(Form\Element\Select\Course::class);
        $form = new Form\Student($selectCourse, $urlHelper('credit/post/student'));

        return new Action\Form\Student($template, $userTable, $creditStudentTable, $recruitTable, $form, 'credit::student-form',$urlHelper);
    }
}
