<?php

namespace Customer\Model;

use JsonSerializable;

class Customer implements JsonSerializable
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
    protected $summary;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Customer
     */
    public function setId(int $id): Customer
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param int $id
     * @return Customer
     */
    public function setType(string $type): Customer
    {
        $this->type = $type;
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
     * @return Customer
     */
    public function setEmail(string $email): Customer
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getAvailableCredits(): string
    {
        return $this->available_credits;
    }

    /**
     * @param string $available_credits
     * @return Customer
     */
    public function setAvailableCredits(int $available_credits): Customer
    {
        $this->available_credits = $available_credits;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreditType(): string
    {
        return $this->credit_type;
    }

    /**
     * @param string $credit_type
     * @return Customer
     */
    public function setCreditType(string $credit_type): Customer
    {
        $this->credit_type = $credit_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getPricingType(): string
    {
        return $this->pricing_type;
    }

    /**
     * @param string $pricing_type
     * @return Customer
     */
    public function setPricingType(string $pricing_type): Customer
    {
        $this->pricing_type = $pricing_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentType(): string
    {
        return $this->payment_type;
    }

    /**
     * @param string $payment_type
     * @return Customer
     */
    public function setPaymentType(string $payment_type): Customer
    {
        $this->payment_type = $payment_type;
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
