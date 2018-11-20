<?php

namespace Recruit\Action\View;

use Recruit\Model;
use Recruit\Model\RecruitTable;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

use Course\Model\CourseTable;

class Single
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\RecruitTable
     */
    private $recruitTable;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var Model\CourseTable
     */
    private $courseTable;

    public function __construct(
        Template\TemplateRendererInterface $template,
        RecruitTable $recruitTable,
		CourseTable $courseTable,
        string $templateName = 'recruit::single'
    ) {
        $this->template = $template;
        $this->recruitTable = $recruitTable;
		$this->courseTable = $courseTable;
        $this->templateName = $templateName;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /**
         * @var string
         */
        $id = $request->getAttribute('id');

        /**
         * @var Model\Recruitr $recruit
         */
        $recruit = $this->recruitTable->fetchById($id);

        if (false === $recruit) {
            return $delegate->process($request);
        }

        return new HtmlResponse($this->template->render($this->templateName, [
            'recruit' => $recruit,
			'courseTable' => $this->courseTable,
        ]));
    }
}
