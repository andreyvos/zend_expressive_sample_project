<?php

namespace User\Action\View;

use User\Model;
use User\Model\User;
use Course;
use Tutor;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Rbac;

class AdminResultSet
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\UserTable
     */
    private $userTable;

    /**
     * @var Course\Model\CourseTable
     */
    private $courseTable;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var string
     */
    private $role;

    public function __construct(
        Template\TemplateRendererInterface $template,
        Model\UserTable $userTable,
        Course\Model\CourseTable $courseTable,
        Tutor\Model\TutorStudentCourseTable $tutorStudentCourseTable,
        string $templateName,
        string $role
    ) {
        $this->template = $template;
        $this->userTable = $userTable;
        $this->courseTable = $courseTable;
        $this->tutorStudentCourseTable = $tutorStudentCourseTable;
        $this->templateName = $templateName;
        $this->role = $role;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        //fetch filter info from GET request if available
        $routeResult = $request->getAttribute(RouteResult::class);
        $selectedCourse = $request->getQueryParams()['course'] ?? 0;
        $user = $request->getAttribute(User::class);
        $filter = $request->getAttribute('filter');

        if ('all' == $filter) {
            $filter = NULL;
        }

        $search = $request->getQueryParams()['search'] ?? [];

        return new HtmlResponse($this->template->render($this->templateName, [
            'resultSet' => $this->userTable->usingSearch(
                $this->role,
                $request->getQueryParams()['search'] ?? []
            ),
            'search' => $search,
            'courseResultSet' => $this->courseTable->fetchAll()
        ]));
    }
}
