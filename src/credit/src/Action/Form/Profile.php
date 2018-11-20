<?php

namespace Credit\Action\Form;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Credit\Form;
use Credit\Model;
use Credit;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Session\Container;
use Zend\Form\FormInterface;
use Zend\Expressive\Helper\UrlHelper;

class Profile
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\UserTable
     */
    private $userTable;

    /**
     * @var Model\UserMetaTable
     */
    private $userMetaTable;

    /**
     * @var FormInterface
     */
    private $form;

    private $urlHelper;

    public function __construct(Template\TemplateRendererInterface $template,         
		Model\CreditUserTable $userTable,
        Model\CreditUserMetaTable $userMetaTable,
		UrlHelper $urlHelper)
		//FormInterface $form)
    {
        $this->template = $template;
        $this->creditUserTable = $userTable;
        $this->creditUserMetaTable = $userMetaTable;
        //$this->form = $form;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        //$customerId = $request->getAttribute('id');

		$user_session = new Container('credit_user');
        $currentUser = $user_session->data;

		if(!isset($user_session->username)) {
			return new RedirectResponse(($this->urlHelper)('credit/form/login'));
		} 

		$customerId = $currentUser->getId();

        if ($customerId) {
            $customer = $this->creditUserTable->oneById($customerId);
        }

        $form = new Form\Profile();

        // bind object when id passed in url
        if (isset($customer) && $customer instanceof Model\CreditUser) {
            //$form->bind($customer);
			$form->setData($customer->getArrayCopy());
        }
		
		//if($currentUser->getRole() == "Rbac\Role\PersonalUser") {
			return new HtmlResponse($this->template->render('credit::profile', [
				'form' => $form,
				'currentUser' => $currentUser
			]));
		//}

		//if($currentUser->getRole() == "Rbac\Role\BusinessUser") {
		//	return new HtmlResponse($this->template->render('credit::profile', [
		//		'form' => $form
		//	]));
		//}
    }
}
