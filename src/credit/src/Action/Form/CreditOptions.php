<?php

namespace Credit\Action\Form;

use Credit\Form;
use Credit\Model;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use User\Model\UserTable;

class CreditOptions
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;
    /**
     * @var Model\OptionsTable
     */
    private $optionsTable;

	private $userTable;

    public function __construct(Template\TemplateRendererInterface $template, Model\CreditOptionsTable $optionsTable, UserTable $userTable)
    {
        $this->template = $template;
        $this->optionsTable = $optionsTable;
		$this->userTable = $userTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
		$tutorResultSet = $this->userTable->byRole('Rbac\Role\Tutor');
        $tutors = array();
        foreach($tutorResultSet as $tutor) {
            $tutors[$tutor->getId()] = $this->userTable->oneById($tutor->getId())->getFirstName() . ' ' . $this->userTable->oneById($tutor->getId())->getLastName();
        }

        $form = new Form\CreditOptions();
        $options = $this->optionsTable->fetchAll()->toArray();
        return new HtmlResponse($this->template->render('credit::options-form', [
            'form' => $form,
            'options' => $options,
            'tutors' => $tutors
        ]));
    }
}
