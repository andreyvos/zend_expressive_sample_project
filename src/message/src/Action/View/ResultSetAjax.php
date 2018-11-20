<?php
/**
 * Created by PhpStorm.
 * User: pujangga
 * Date: 31/08/18
 * Time: 14:59
 */

namespace Message\Action\View;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Message\Model;
use Psr\Http\Message\ServerRequestInterface;
use User;
use Zend\Diactoros\Response\JsonResponse;

class ResultSetAjax
{
    /**
     * @var User\Model\UserTable
     */
    private $userTable;

    /**
     * @var Model\MessageTable
     */
    private $messageTable;

    /**
     * @var User\Model\UserOnlineTable
     */
    private $userOnlineTable;

    public function __construct(User\Model\UserTable $userTable,
                                User\Model\UserOnlineTable $userOnlineTable,
                                Model\MessageTable $messageTable)
    {
        $this->userTable = $userTable;
        $this->userOnlineTable = $userOnlineTable;
        $this->messageTable = $messageTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /** @var User\Model\User $currentUser */
        $currentUser = $request->getAttribute(User\Model\User::class);
        $currentUserId = $currentUser->getId();

        $this->userOnlineTable->add($currentUserId);
        $this->userOnlineTable->clean();

        $text = $request->getQueryParams()['text'] ?? '';
        $type = $request->getQueryParams()['type'] ?? 'mail';
        $page = $request->getQueryParams()['page'] ?? 1;

        if ($type === 'mail') {
            $messages = $this->messageTable->getMessages($text, $currentUser, intval($page));
            return new JsonResponse($messages);
        }

        if ($type === 'contact') {
            $contacts = $this->messageTable->getContacts($text, $currentUser, intval($page));
            return new JsonResponse($contacts);
        }

        if ($type === 'detail') {
            $messageIds = $request->getQueryParams()['ids'] ?? '';
            $details = $this->messageTable->getMessageDatails(explode(',', $messageIds), $currentUser);
            return new JsonResponse($details);
        }

        return new JsonResponse([]);
    }
}