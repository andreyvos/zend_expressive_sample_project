<?php

namespace Recruit\Action\View;

use Recruit\Model;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

use Rbac;
use Recruit\Model\RecruitTable;
use Course\Model\CourseTable;

class ResultSet
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\RecruitTable
     */
    private $customerTable;

    /**
     * @var Model\CourseTable
     */
    private $courseTable;

    public function __construct(
        Template\TemplateRendererInterface $template,
        RecruitTable $recruitTable,
		CourseTable $courseTable
        )
    {
        $this->template = $template;
        $this->recruitTable = $recruitTable;
		$this->courseTable = $courseTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {	
		
        return new HtmlResponse($this->template->render('recruit::list', [
            'resultSet' => $this->recruitTable->fetchAll(),
			'courseTable' => $this->courseTable,
        ]));
    }
}
