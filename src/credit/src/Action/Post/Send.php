<?php

namespace Credit\Action\Post;

use Credit\InputFilter;
use Credit\Model;
use Credit;
use Options;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use User\Model\UserTable;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Expressive\Template;

class Send
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
     * @var Model\CreditUserTable
     */
    private $creditUserTable;


    /**
     * @var UserTable
     */
    private $userTable;
  
	/**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var Options
     */
    private $optionsTable;

    /**
     * @var Mail\Transport\TransportInterface
     */
    private $transportMail;

    public function __construct(
        Template\TemplateRendererInterface $template,
        UrlHelper $urlHelper,
        Model\RecruitTable $recruitTable,
		Credit\Model\CreditUserTable $creditUserTable,
        Options\Model\OptionsTable $optionsTable,
        UserTable $userTable,
        Mail\Transport\TransportInterface $transportMail)
    {
        $this->template = $template;
        $this->urlHelper = $urlHelper;
        $this->recruitTable = $recruitTable;
		$this->creditUserTable = $creditUserTable;
        $this->optionsTable = $optionsTable;
        $this->userTable = $userTable;
        $this->transportMail = $transportMail;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $data = $request->getParsedBody() ?? [];

		$credit_courses = $data['credit_courses'];

		$list = array();
		foreach($credit_courses as $course) {
			$list[$course['courseId']] = $course['credit'];
		}


		$json_encode = json_encode($list);		
		$data['credit_courses'] = $json_encode;

		$length = 8;
        $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $retVal = "";

		do {
			for ($n=0;$n<=$length;$n++) {
				$retVal .= substr($charset, rand(1, strlen($charset)), 1);
			}
			$retValFinal = $retVal;
		} while( $this->creditUserTable->byPlainpin($retValFinal)->current() );

		$data['pin'] = $retValFinal;
		$data['username'] = $data['identity'];

		// email exist on table user
        $userData = $this->userTable->byIdentity($data['identity'])->current();
        if (!empty($userData)) {
            return new JsonResponse([
                'errors' => ['identity' => ['duplicate' => "Email Already registed as {$userData->getRole()}!"]]
            ]);
        }

		$sendmail = false;
        $role = $data['role'];

        if ($data['id'] == '') {
            if ($role == 'personal') {
                $data['role'] = $role;
            } elseif ($role == 'business') {
                $data['role'] = $role;
            } else {
            	$data['role'] = 'Rbac\Role\CreditUser';
            }
			$sendmail = true;
		} else {
			$user = $this->creditUserTable->byIdentity($data['identity'])->current();
			$data['pin'] = $user->getPlainPin();		
		}
		
        $filter = new InputFilter\Recruit();
        $filter->setData($data);
		
        if (!$filter->isValid()) {
            return new JsonResponse([
                'errors' => $filter->getMessages()
            ]);
        }	
		
		$response = $this->creditUserTable->save($filter->getValues());

        if ('duplicate_identity' === $response) {
            return new JsonResponse([
                'errors' => ['identity' => ['duplicate' => 'Email Already Exists!']]
            ]);
        } elseif ('duplicate_username' === $response) {
            return new JsonResponse([
                'errors' => ['username' => ['duplicate' => 'Username Already Exists!']]
            ]);
        } elseif ('duplicate_pin' === $response) {
            return new JsonResponse([
                'errors' => ['pin' => ['duplicate' => 'Pin Already Exists!']]
            ]);
        }
		
		if($data['id'] == '') {
			$data['credit_user_id'] = $response;
		} else {
			$data['id'] = $data['recruitId'];
		}

		if(!isset($data['courses']) || $data['courses'] == '')
		{
			$data['courses'] = 0;
		}

		$filter->setData($data);
        if (!$filter->isValid()) {
            return new JsonResponse([
                'errors' => $filter->getMessages()
            ]);
        }
		
		$recruitId = $this->recruitTable->save($filter->getValues());		

        $email = $filter->getValues()['identity'] ?? '';
		
		if($sendmail) {
			$htmlMarkup = $this->template->render('credit::emails/newrecruit', [
				'layout' => false,
				'pin' => $data['pin'],
				'role' => $role,
				'registerLink' => $request->getUri()->getHost() . ($this->urlHelper)('credit/home') 
			]);
			$html = new MimePart($htmlMarkup);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->addPart($html);

			if ($this->optionsTable->optionExists('from_email')) {
				$from = $this->optionsTable->fetchByName('from_email')['value'];
				$message = new Mail\Message();
				$message->addTo($email)
						->addFrom($from, 'NCC Home Learning')
						->setSubject('Welcome to NCC Home Learning')
						->setBody($body);

				$status = $this->transportMail->send($message);

			}
		}
			
        return new JsonResponse([
            'redirectTo' => ($this->urlHelper)('client/view/user')
        ]);
    }
}
