<?php

namespace User\Action\Form;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Options\Model\OptionsTable;
use Psr\Http\Message\ServerRequestInterface;
use User\Form;
use User;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Zend\Diactoros\Response\RedirectResponse;

class Login
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /** @var OptionsTable */
    private $optionsTable;

    public function __construct(Template\TemplateRendererInterface $template, OptionsTable $optionsTable)
    {
        $this->template = $template;
        $this->optionsTable = $optionsTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /**
         * @var User\Model\User $currentUser
         */
        $currentUser = $request->getAttribute(User\Model\User::class);
        if ($currentUser && $currentUser->getId() > 0) {
            return new RedirectResponse('/');
        }

        $logoData = $this->optionsTable->fetchByName('website_logo');
        $logo = !empty($logoData['value']) ? $logoData['value'] : null;

        $query = $request->getQueryParams();
        $redirect = !empty($query['redirect']) ? $query['redirect'] : '';

        $form = new Form\Login();
        return new HtmlResponse($this->template->render('user::login', [
            'form' => $form,
            'logo' => $logo,
            'redirect' => $redirect
        ]));
    }
}
