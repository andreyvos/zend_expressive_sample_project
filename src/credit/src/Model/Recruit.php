<?php

namespace Credit\Model;

use Credit;
use Zend\Filter\Word\UnderscoreToCamelCase;

class Recruit
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $credit_user_id;

    /**
     * @var int
     */
    protected $courses;

    /**
     * @var string
     */
    protected $credit_courses;

    /**
     * @var int
     */
    protected $createdAt;

	protected $identity;

	protected $creditUser;

    /**
     * @param array $input
     */
    public function exchangeArray(array $input)
    {
        if (empty($input[0])) {
            $this->setId($input['id']);
			$this->setCreditUserId($input['credit_user_id']);
            $this->setCourses($input['courses']);
			$this->setCreditCourses($input['credit_courses']);
            return;
        }
        $this->setId($input[0]['id']);
		$this->setCreditUserId($input[0]['credit_user_id']);
		$this->setCourses($input[0]['courses']);
		$this->setCreditCourses($input[0]['credit_courses']);

        $underscoreToCamelCase = new UnderscoreToCamelCase();

        /**
         * @var UserMeta $meta
         */
        foreach ($input[1] ?? [] as $meta) {
            $name = $underscoreToCamelCase->filter($meta->getName());

            $this->meta[$meta->getName()] = $meta->getValue();
            $this->metaInCamelCase[$name] = $meta->getValue();
        }

    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Recruit
     */
    public function setId(int $id): Recruit
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreditUserId(): int
    {
        return $this->credit_user_id;
    }

    /**
     * @param int $id
     * @return Recruit
     */
    public function setCreditUserId(int $credit_user_id): Recruit
    {
        $this->credit_user_id = $credit_user_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getCourses(): int
    {
        return $this->courses;
    }

    /**
     * @param int $id
     * @return Recruit
     */
    public function setCourses(int $courses): Recruit
    {
        $this->courses = $courses;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreditCourses(): string
    {
        return $this->credit_courses;
    }

    /**
     * @param int $id
     * @return Recruit
     */
    public function setCreditCourses(string $credit_courses): Recruit
    {
        $this->credit_courses = $credit_courses;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreditCoursesArray()
    {
        return json_decode($this->credit_courses,true);
    }

    /**
     * @param int $id
     * @return Recruit
     */
    public function setCreditCoursesArray(string $credit_courses): Recruit
    {
        $this->credit_courses = json_encode($credit_courses);
        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     *
     * @return Recruit
     */
    public function setCreatedAt(int $createdAt): Recruit
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Return array for object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
