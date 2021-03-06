<?php

namespace Credit\Action\Post;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Credit\Model;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;

class Newpass
{

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var Model\CreditUserTable
     */
    private $creditUserTable;

    public function __construct(UrlHelper $urlHelper, Model\CreditUserTable $creditUserTable)
    {
        $this->urlHelper = $urlHelper;
        $this->creditUserTable = $creditUserTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $data = $request->getParsedBody() ?? [];
        $errors = [];
        if (empty($data['pin'])) {
            $errors['pin'] = [];
        }
        if (empty($data['password'])) {
            $errors['password'] = [];
        }
        if (empty($data['vpassword'])) {
            $errors['vpassword'] = [];
        }
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => $errors]);
        }
        if (strlen($data['password']) < 5) {
            return new JsonResponse(['errorMessage' => 'Password must be of minimum 5 characters length']);
        }
        if ($data['vpassword'] !== $data['password']) {
            return new JsonResponse(['errorMessage' => 'Passwords must match']);
        }

        $users = $this->creditUserTable->byToken($data['pin'])->toArray();

        if (0 == count($users)) {
            return new JsonResponse(['errorMessage' => 'Failed, try again later']);
        }

        $user = reset($users);

        $this->creditUserTable->updatePassword($user['id'], $data['password']);
        $this->creditUserTable->updateToken($user['id'], '');

        return new JsonResponse([
            'redirectTo' => ($this->urlHelper)('credit/form/login')
        ]);
    }
}
