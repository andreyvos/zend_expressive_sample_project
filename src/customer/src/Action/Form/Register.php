<?php

namespace Customer\Action\Form;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Customer\Form;
use Customer;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Zend\Diactoros\Response\RedirectResponse;

class Register
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    public function __construct(Template\TemplateRendererInterface $template)
    {
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /**
         * @var Customer\Model\Customer $currentCustomer
         */
        $currentCustomer = $request->getAttribute(Customer\Model\Customer::class);
        if ($currentCustomer && $currentCustomer->getId() > 0) {
            return new RedirectResponse('/');
        }

        $form = new Form\Customer();

        return new HtmlResponse($this->template->render('customer::register', [
            'form' => $form,
            'token' => $request->getQueryParams()['token'] ?? ''
        ]));
    }
}
