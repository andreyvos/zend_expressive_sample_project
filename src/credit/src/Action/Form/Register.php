<?php

namespace Credit\Action\Form;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Credit\Form;
use Credit\Model;
use Credit\Model\CreditUser;
use Credit;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Form\Element\Hidden;
use Zend\Session\Container;
use Zend\Expressive\Helper\UrlHelper;

class Register
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\UserTable
     */
    private $userTable;

    private $urlHelper;

    public function __construct(
		Template\TemplateRendererInterface $template,
		Model\CreditUserTable $userTable,
		UrlHelper $urlHelper
		)
    {
        $this->template = $template;
		$this->creditUserTable = $userTable;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /**
         * @var User\Model\User $currentUser
         */
		$user_session = new Container('credit_user');
		$currentUser = $user_session->data;

		if(!isset($user_session->username)) {
			return new RedirectResponse(($this->urlHelper)('credit/form/login'));
		} 

		$currentUserId = $currentUser->getId();	 

		$user = $this->creditUserTable->oneById($currentUserId);
		$role = $user->getRole();

        if ($role == 'Rbac\Role\BusinessUser' || $role == 'Rbac\Role\PersonalUser') {
        	return new RedirectResponse('/credit');
        }

        $form = new Form\Register();
        if (isset($user) && $user instanceof CreditUser) {
            $form->bind($user);
        }

        if (in_array($role, ['personal', 'business'])) {
            $roleOptions = $form->get('role')->getOptions();
            foreach ($roleOptions['value_options'] as $key => $option) {
                $roleOptions['value_options'][$key]['selected'] = $roleOptions['value_options'][$key]['value'] === $role;
            }

            $form->get('role')->setAttribute('disabled', true);
            $form->get('role')->setOptions($roleOptions);
            $form->get('user_role')->setValue($role);
        }

        return new HtmlResponse($this->template->render('credit::register', [
            'form' => $form,
			'role' => $role,
			'identity' => $currentUser->getIdentity()
        ]));
    }
}
