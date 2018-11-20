<?php

namespace Options\Model;

class Options
{
    const FIST_ASSIGNMENT_NOTIF_TEMPLATE_TITLE = 'Student Assignment Warning';
    const FIST_ASSIGNMENT_NOTIF_TEMPLATE_BODY = <<<HTML
Hi <b>{tutor_name}</b>,

<p>The following students feedback requires marking within the next 24 hours, otherwise it will fall outside the agreed timeframe for marking:</p>

<ul>
<li>Student name: <b>{student_name}</b></li>
<li>Course name: <b>{course}</b></li>
<li>Course Module: <b>{course_module}</li>
<li>Assignment upload date: <b>{assignment_upload_date}</b></li>
<li>Due for marking: <b>{date_marking_due}</b></li>
</ul>

<p>Please <a href="{marking_link}">click here</a> to mark the work.</p>
<p>Thank you for your ongoing support.</p>
HTML;

    const SECOND_ASSIGNMENT_NOTIF_TEMPLATE_TITLE = 'Student Assignment Warning';
    const SECOND_ASSIGNMENT_NOTIF_TEMPLATE_BODY = <<<HTML
Hi <b>{tutor_name}</b>,

<p>The following students work has not been marked within the agreed timeline for marking, please insure that any outstanding work is marked within 24 hours.</p>

<ul>
<li>Student name: <b>{student_name}</b></li>
<li>Course name: <b>{course}</b></li>
<li>Course Module: <b>{course_module}</li>
<li>Assignment upload date: <b>{assignment_upload_date}</b></li>
<li>Due for marking: <b>{date_marking_due}</b></li>
</ul>

<p>Please <a href="{marking_link}">click here</a> to mark the work.</p>
<p>Thank you for your ongoing support.</p>
HTML;

    const THIRD_ASSIGNMENT_NOTIF_TEMPLATE_TITLE = 'Student Assignment Final Warning';
    const THIRD_ASSIGNMENT_NOTIF_TEMPLATE_BODY = <<<HTML
Hi <b>{tutor_name}</b>,

<p>The following students work has not been marked within the agreed timeline for marking, please insure that any outstanding work is marked within 24 hours. We will be unable to provide additional students/work if the work remains unmarked for a further 24 hours.</p>

<ul>
<li>Student name: <b>{student_name}</b></li>
<li>Course name: <b>{course}</b></li>
<li>Course Module: <b>{course_module}</li>
<li>Assignment upload date: <b>{assignment_upload_date}</b></li>
<li>Due for marking: <b>{date_marking_due}</b></li>
</ul>

<p>Please <a href="{marking_link}">click here</a> to mark the work.</p>
<p>Thank you for your ongoing support.</p>
HTML;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Options
     */
    public function setId(int $id): Options
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Options
     */
    public function setName(string $name): Options
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return Options
     */
    public function setValue(string $value): Options
    {
        $this->value = $value;
        return $this;
    }
}
