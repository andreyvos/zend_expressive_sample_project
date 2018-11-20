<?php

namespace Customer\Action\View;

use Customer\Model;
use Customer\Model\CustomerTable;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class Single
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\CustomerTable
     */
    private $customerTable;

    /**
     * @var string
     */
    private $templateName;

    public function __construct(
        Template\TemplateRendererInterface $template,
        CustomerTable $customerTable,
        string $templateName = 'customer::single/write'
    ) {
        $this->template = $template;
        $this->customerTable = $customerTable;
        $this->templateName = $templateName;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /**
         * @var string
         */
        $id = $request->getAttribute('id');

        /**
         * @var Model\Customer $customer
         */
        $customer = $this->customerTable->fetchById($id);

        if (false === $customer) {
            return $delegate->process($request);
        }

        return new HtmlResponse($this->template->render($this->templateName, [
            'customer' => $customer
        ]));
    }
}
