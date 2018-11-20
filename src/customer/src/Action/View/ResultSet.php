<?php

namespace Customer\Action\View;

use Customer\Model;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

use Rbac;
use Customer\Model\CustomerTable;

class ResultSet
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\CustomerTable
     */
    private $customerTable;

    public function __construct(
        Template\TemplateRendererInterface $template,
        CustomerTable $customerTable
        )
    {
        $this->template = $template;
        $this->customerTable = $customerTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return new HtmlResponse($this->template->render('customer::resultset', [
            'resultSet' => $this->customerTable->fetchAll(),
        ]));
    }
}
