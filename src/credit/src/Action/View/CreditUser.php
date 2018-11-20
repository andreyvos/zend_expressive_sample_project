<?php

namespace Credit\Action\View;

use Credit\Model;
use Credit\Model\CreditUserTable;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

use Rbac;

class CreditUser
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\CreditUserTable
     */
    private $creditUserTable;

    public function __construct(
        Template\TemplateRendererInterface $template,
        CreditUserTable $creditUserTable
        )
    {
        $this->template = $template;
        $this->creditUserTable = $creditUserTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {	
		
        return new HtmlResponse($this->template->render('credit::user-resultset', [
            'resultSet' => $this->creditUserTable->fetchAll()
        ]));
    }
}
