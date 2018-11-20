<?php

namespace Customer\Action\Form;

use Customer\Form;
use Customer\Model;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class Customer
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\CustomerTable
     */
    private $customerTable;

    public function __construct(Template\TemplateRendererInterface $template, Model\CustomerTable $customerTable)
    {
        $this->template = $template;
        $this->customerTable = $customerTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $customerId = $request->getAttribute('id');

        if ($customerId) {
            /**
             * @var Model\Customer
             */
            $customer = $this->customerTable->fetchById($customerId);
        }

        $form = new Form\Customer();

        // bind object when id passed in url
        if (isset($customer) && $customer instanceof Model\Customer) {
            $form->bind($customer);
        }

        return new HtmlResponse($this->template->render('customer::form', [
            'form' => $form
        ]));
    }
}
