<?php

namespace Credit\Action\View;

use Credit\Model;
use Credit\Model\CreditUserTable;
use Credit\Model\RecruitTable;
use Course\Model\CourseTable;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class CreditUserDetails
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\CreditUserTable
     */
    private $creditUserTable;

    /**
     * @var Model\RecruitTable
     */
    private $recruitTable;

    /**
     * @var Model\CourseTable
     */
    private $courseTable;

    public function __construct(
        Template\TemplateRendererInterface $template,
        CreditUserTable $creditUserTable,
        RecruitTable $recruitTable,
		CourseTable $courseTable
        )
    {
        $this->template = $template;
        $this->creditUserTable = $creditUserTable;
        $this->recruitTable = $recruitTable;
		$this->courseTable = $courseTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {	
		$data = $request->getParsedBody() ?? [];

		$userId = $request->getAttribute('id');
		
        return new HtmlResponse($this->template->render('credit::user-details', [
            'result' => $this->creditUserTable->oneById($userId),
            'recruit' => $this->recruitTable->fetchByCreditUserId($userId),
			'courseTable' => $this->courseTable,
        ]));
    }
}
