<?php

namespace Credit\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template;
use Zend\Session\Container;

class Logout
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    public function __construct(Template\TemplateRendererInterface $template, UrlHelper $urlHelper, AuthenticationService $authenticationService)
    {
        $this->template = $template;
        $this->urlHelper = $urlHelper;
        $this->authenticationService = $authenticationService;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $session_user = new Container('credit_user');

		//destroy all
		if('all' == $request->getAttribute('instruction')) {
			$session_user->getManager()->destroy();
			$this->authenticationService->clearIdentity();
		}
		
		$user = $session_user->data;
		$role = $user->getRole();

		//clear user
		$session_user->getManager()->getStorage()->clear('credit_user');

		$originalAdmin = new Container('admin_user');
		
		$goHome = false;
		$count = is_array($originalAdmin->userChain) ? count($originalAdmin->userChain) : 0;
		if($count > 0) {
			$goHome = true;
		}

		$originalAdmin->getManager()->getStorage()->clear('admin_user');
		
		$originalUser = new Container('OriginalUser');

        //find out if we're a "Login As" candidate
        $count = is_array($originalUser->userChain) ? count($originalUser->userChain) : 0;
        if($count > 0) {
            $newUser = $originalUser->userChain[$count-1];
            array_pop($originalUser->userChain);

            $originalUser->currentUser = $newUser;

            $this->authenticationService->clearIdentity();
            $storage = $this->authenticationService->getStorage();
            $storage->write($newUser);
            return new RedirectResponse(($this->urlHelper)('home'));
        }

        //no users above us in the chain, log out completely
        $originalUser->userChain = array();
        $originalUser->currentUser = array();
        $originalUser->topLevelRole = null;

		if($goHome) {
			return new RedirectResponse(($this->urlHelper)('home'));
		}

		if($role == "Rbac\\Role\\PersonalUser") {
			$this->authenticationService->clearIdentity();
		}

        return new RedirectResponse(($this->urlHelper)('credit/form/login'));
    }
}
