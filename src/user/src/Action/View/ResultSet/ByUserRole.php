<?php

namespace User\Action\View\ResultSet;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class ByUserRole
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * ByUserRole constructor.
     * @param Template\TemplateRendererInterface $template
     */
    public function __construct(Template\TemplateRendererInterface $template)
    {
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return new HtmlResponse($this->template->render('student::resultset'));
    }
}
