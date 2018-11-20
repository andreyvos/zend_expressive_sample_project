<?php

namespace Customer\Action\Post;

use Customer\InputFilter;
use Customer\Model;
use Options;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Expressive\Template;

class Customer
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\CustomerTable
     */
    private $customerTable;

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
        Model\CustomerTable $customerTable,
        Options\Model\OptionsTable $optionsTable,
        Mail\Transport\TransportInterface $transportMail)
    {
        $this->template = $template;
        $this->urlHelper = $urlHelper;
        $this->customerTable = $customerTable;
        $this->optionsTable = $optionsTable;
        $this->transportMail = $transportMail;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $data = $request->getParsedBody() ?? [];

        $filter = new InputFilter\Customer();
        $filter->setData($data);

        if (!$filter->isValid()) {
            return new JsonResponse([
                'errors' => $filter->getMessages()
            ]);
        }

        $customerId = $this->customerTable->save($filter->getValues());
        $email = $filter->getValues()['email'] ?? '';

        $token = $this->customerTable->generateToken($customerId);
        $this->customerTable->updateToken($customerId, $token);

        $site_name_long = $this->optionsTable->optionExists('site_name_long') ? $this->optionsTable->fetchByName('site_name_long')['value'] : '';
        $site_name = $this->optionsTable->optionExists('site_name') ? $this->optionsTable->fetchByName('site_name')['value'] : '';
        $site_url = $this->optionsTable->optionExists('site_url') ? $this->optionsTable->fetchByName('site_url')['value'] : '';
        $from = $this->optionsTable->optionExists('from_email') ? $this->optionsTable->fetchByName('from_email')['value'] : '';

        $htmlMarkup = $this->template->render('emails::newcustomer', [
            'layout' => false,
            'registerLink' => $request->getUri()->getHost() . ($this->urlHelper)('customer/form/register') . '?token=' . $token,
            'site_name_long' => $site_name_long,
            'site_name' => $site_name,
            'site_url' => $site_url
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
            'redirectTo' => ($this->urlHelper)('customer/view/single', ['id' => $customerId])
        ]);
    }
}
