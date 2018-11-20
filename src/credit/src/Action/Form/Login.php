<?php

namespace Credit\Action\Form;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Credit\Form;
use Credit;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Session\Container;

class Login
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
         * @var User\Model\User $currentUser
         */
		$user_session = new Container('credit_user');
		$currentUser = $user_session->username;
        if ($currentUser) {
            return new RedirectResponse('/credit');
        }
        $form = new Form\Login();
        return new HtmlResponse($this->template->render('credit::login', [
            'form' => $form
        ]));
    }
}
