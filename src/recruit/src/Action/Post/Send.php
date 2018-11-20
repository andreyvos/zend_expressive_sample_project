<?php

namespace Recruit\Action\Post;

use Recruit\InputFilter;
use Recruit\Model;
use Options;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
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
        Options\Model\OptionsTable $optionsTable,
        Mail\Transport\TransportInterface $transportMail)
    {
        $this->template = $template;
        $this->urlHelper = $urlHelper;
        $this->recruitTable = $recruitTable;
        $this->optionsTable = $optionsTable;
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

		//var_dump(array_values($list));

		$json_encode = json_encode($list);		
		$data['credit_courses'] = $json_encode;
		
        $filter = new InputFilter\Recruit();
        $filter->setData($data);

		
		
        if (!$filter->isValid()) {
            return new JsonResponse([
                'errors' => $filter->getMessages()
            ]);
        }
		
		//var_dump($filter->getValues());

        $customerId = $this->recruitTable->save($filter->getValues());
        $email = $filter->getValues()['email'] ?? '';

        $site_name_long = $this->optionsTable->optionExists('site_name_long') ? $this->optionsTable->fetchByName('site_name_long')['value'] : '';
        $site_name = $this->optionsTable->optionExists('site_name') ? $this->optionsTable->fetchByName('site_name')['value'] : '';
        $site_url = $this->optionsTable->optionExists('site_url') ? $this->optionsTable->fetchByName('site_url')['value'] : '';
        $from = $this->optionsTable->optionExists('from_email') ? $this->optionsTable->fetchByName('from_email')['value'] : '';

        $htmlMarkup = $this->template->render('emails::newcustomer', [
            'layout' => false,
            'site_name_long' => $site_name_long,
            'site_name' => $site_name,
            'site_url' => $site_url
            //'registerLink' => $request->getUri()->getHost() . ($this->urlHelper)('recruit/form/register') . '?token=' . $token
        ]);
        $html = new MimePart($htmlMarkup);
        $html->type = "text/html";

        $body = new MimeMessage();
        $body->addPart($html);

        if (! empty($from)) {
            $message = new Mail\Message();
            $message->addTo($email)
                ->addFrom($from)
                ->setSubject('Welcome to ' . $site_name_long)
                ->setBody($body);
            $this->transportMail->send($message);
        }
			
        return new JsonResponse([
            'redirectTo' => ($this->urlHelper)('recruit/view/single', ['id' => $customerId])
        ]);
    }
}
