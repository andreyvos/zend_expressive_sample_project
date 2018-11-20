<?php

namespace Message\Action\View;

use Message\Model;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use User;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class ResultSet
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
        return new HtmlResponse($this->template->render('message::resultset'));
    }
}
