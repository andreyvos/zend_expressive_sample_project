<?php

namespace Assignment\Action\View\Work;

use Assignment\Model\AssignmentWorkTable;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rbac;
use User\Model\User;
use User\Model\UserMetaTable;
use User\Model\UserTable;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Template;

class ResultSet
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var AssignmentWorkTable
     */
    private $assignmentWorkTable;

    /**
     * @var UserTable
     */
    private $userTable;

    /**
     * @var UserMetaTable
     */
    private $userMetaTable;

    public function __construct(Template\TemplateRendererInterface $template,
                                UserTable $userTable,
                                UserMetaTable $userMetaTable,
                                AssignmentWorkTable $assignmentWorkTable)
    {
        $this->template = $template;
        $this->userTable = $userTable;
        $this->userMetaTable = $userMetaTable;
        $this->assignmentWorkTable = $assignmentWorkTable;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|JsonResponse
     * @throws \ReflectionException
     */
    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $params = $request->getQueryParams();
        $params['status'] = $request->getAttribute('filter');

        /** @var User $user */
        $user = $request->getAttribute(User::class);
        $role = $user->getRole();
        if ($role == Rbac\Role\Tutor::class) {
            $params['tutor'] = $user->getId();
        }

        if (!empty($params['ajax'])) {
            $resultSet = $this->assignmentWorkTable->usingFilter($params);
            return new JsonResponse($resultSet);
        }

        $tutorsResultSet = [];
        if ($role == Rbac\Role\Administrator::class) {
            /** @var User[] $tutors */
            $tutors = $this->userTable->byRole(Rbac\Role\Tutor::class);
            foreach ($tutors as $tutor) {
                $tutorfirstname = $this->userMetaTable->getMetaByName($tutor->getId(), 'first_name')->toArray();
                $tutorlastname = $this->userMetaTable->getMetaByName($tutor->getId(), 'last_name')->toArray();
                $tutorfirstname = empty($tutorfirstname) ? "" : $tutorfirstname[0]['value'];
                $tutorlastname = empty($tutorlastname) ? "" : $tutorlastname[0]['value'];

                $tutorsResultSet[] = [
                    "id" => $tutor->getId(),
                    "selected" => !empty($params['tutor']) && $params['tutor'] == $tutor->getId(),
                    "first_name" => $tutorfirstname,
                    "last_name" => $tutorlastname
                ];
            }
        }

        return new HtmlResponse($this->template->render('assignment::work/resultset', [
            'tutorResultSet' => $tutorsResultSet,
            'isTutor' => $role == Rbac\Role\Tutor::class
        ]));
    }
}
