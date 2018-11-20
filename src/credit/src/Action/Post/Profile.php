<?php

namespace Credit\Action\Post;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Credit\InputFilter;
use Credit\Model;
use Zend\Authentication;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Session\Container;
use User;

class Profile
{
    /**
     * @var Authentication\AuthenticationService
     */
    private $authenticationService;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var Model\UserTable
     */
    private $userTable;
    /**
     * @var Model\UserTable
     */
    private $creditUserTable;


    /**
     * @var Model\UserMetaTable
     */
    private $userMetaTable;

    public function __construct(
        Authentication\AuthenticationService $authenticationService,
        UrlHelper $urlHelper,
        Model\CreditUserTable $creditUserTable,
        Model\CreditUserMetaTable $userMetaTable,
		User\Model\UserTable $userTable
    )
    {
        $this->authenticationService = $authenticationService;
        $this->creditUserTable = $creditUserTable;
        $this->creditUserMetaTable = $userMetaTable;
		$this->userTable = $userTable;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $data = $request->getParsedBody() ?? [];

		$user_session = new Container('credit_user');
        $currentUser = $user_session->data;

		$customerId = $currentUser->getId();

        if ($customerId) {
            $customer = $this->creditUserTable->oneById($customerId);
        }

		$data['id'] = $currentUser->getId();
		$userId = $data['id'];
		$data['username'] = $currentUser->getUsername();
		$data['identity'] = $currentUser->getIdentity();
		if($data['password'] == '' or is_null($data['password'])) {
			unset($data['password']);
		}

		if($currentUser->getRole() == "Rbac\Role\PersonalUser")  {
			$filter = new InputFilter\ProfilePersonal();
		} else {
			$filter = new InputFilter\ProfileBusiness();
		}
        $filter->setData($data);

        if (!$filter->isValid()) {
            return new JsonResponse([
                'errors' => $filter->getMessages()
            ]);
        }

		//var_dump($filter->getValues());

		$result = $this->creditUserTable->save($filter->getValues());
		if ($currentUser->getRole() == "Rbac\Role\PersonalUser") {
			//$this->userTable->save($filter->getValues());
		}

		return new JsonResponse([
                'redirectTo' => ($this->urlHelper)('credit/view/success')
        ]);

    }
}
