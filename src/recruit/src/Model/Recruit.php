<?php

namespace Recruit\Model;

use JsonSerializable;

class Recruit implements JsonSerializable
{
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
    protected $email;

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

    /**
     * @param array $input
     */
    public function exchangeArray(array $input)
    {
        if (empty($input[0])) {
            $this->setId($input['id']);
            $this->setName($input['name']);
            $this->setEmail($input['email']);
            $this->setCourses($input['courses']);
            return;
        }
        $this->setId($input[0]['id']);
        $this->setName($input[0]['name']);
        $this->setEmail($input[0]['email']);
		$this->setCourses($input[0]['courses']);

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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $email
     * @return Recruit
     */
    public function setName(string $name): Recruit
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Recruit
     */
    public function setEmail(string $email): Recruit
    {
        $this->email = $email;
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
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getArrayCopy();
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
