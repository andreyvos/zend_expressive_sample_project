<?php

namespace Credit\Action\Post;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Credit\Model;
use User;
use Zend\Authentication;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Session\Container;

class Authenticate
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
     * @var Model\CreditUserTable
     */
    private $creditUserTable;

    /**
     * @var Model\UserTable
     */
    private $userTable;

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
        $this->userTable = $userTable;
        $this->creditUserTable = $creditUserTable;
        $this->creditUserMetaTable = $userMetaTable;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $data = $request->getParsedBody() ?? [];

		if(isset( $data['password'])) {
			$inputpassword = $data['password'];
		}

		$defaultJsonResponse = new JsonResponse([
            'errors' => [
                'identity' => [],
                'password' => []
            ],
            'errorMessage' => 'Login failed'
        ]);

        if (isset($data['pin'])) {
            $defaultJsonResponse = new JsonResponse([
                'errors' => [
                    'pin' => []
                ],
                'errorMessage' => 'Login failed'
            ]);
        }

        if ((empty($data['identity']) || empty($data['password'])) && empty($data['pin'])) {
            return $defaultJsonResponse;
        }

		$user_session = new Container('credit_user');

		if (isset($data['identity'])) {
			$user = $this->creditUserTable->byIdentity($data['identity'])->current();
			$savedHash = $user->getPassword();

			
			if(!password_verify($inputpassword, $savedHash)) {
				return $defaultJsonResponse;
			}

		} else {
			$user = $this->creditUserTable->byPlainpin($data['pin'])->current();
		}

		if($user) {
			$login = $this->creditUserTable->oneById($user->getId());
			$user_session->data = $login;
			$user_session->username = $user->getUsername();

			if($login->getRole() == "Rbac\\Role\\PersonalUser" && !empty($login->student)) {
				$student = $this->userTable->oneById($login->student[0]);
				//log existing user out
				$this->authenticationService->clearIdentity();
				//push new user to the login state
				$storage = $this->authenticationService->getStorage();
				$storage->write($student);
			}
		}

        if (isset($user_session->username)) {

            return new JsonResponse([
                'redirectTo' => ($this->urlHelper)('credit/home')
            ]);
        }

        return $defaultJsonResponse;
    }
}
