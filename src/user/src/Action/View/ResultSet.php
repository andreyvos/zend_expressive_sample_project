<?php
namespace User\Action\View;

use Course;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class ResultSet
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Course\Model\CourseTable
     */
    private $courseTable;

    public function __construct(Template\TemplateRendererInterface $template, Course\Model\CourseTable $courseTable)
    {
        $this->template = $template;
        $this->courseTable = $courseTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return new HtmlResponse($this->template->render('tutor::resultset', [
            'courseResultSet' => $this->courseTable->fetchAll()
        ]));
    }
}
