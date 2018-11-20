<?php

namespace Credit\Model;

class CreditUserMeta
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $userId;

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
     * @return CreditUserMeta
     */
    public function setId(int $id): CreditUserMeta
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return CreditUserMeta
     */
    public function setUserId(int $userId): CreditUserMeta
    {
        $this->userId = $userId;
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
     * @return CreditUserMeta
     */
    public function setName(string $name): CreditUserMeta
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
     * @return CreditUserMeta
     */
    public function setValue(string $value): CreditUserMeta
    {
        $this->value = $value;
        return $this;
    }
}
