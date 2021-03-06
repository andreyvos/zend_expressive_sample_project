<?php

namespace Credit\Action\Form;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Credit\Form;
use Credit;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Zend\Diactoros\Response\RedirectResponse;

class Newpass
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
        $currentUser = $request->getAttribute(Credit\Model\CreditUser::class);
        if ($currentUser && $currentUser->getId() > 0) {
            return new RedirectResponse('/credit');
        }

        $form = new Form\Newpass();

        return new HtmlResponse($this->template->render('credit::newpass', [
            'form' => $form,
            'pin' => $request->getQueryParams()['pin'] ?? ''
        ]));
    }
}
