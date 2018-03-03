<?php

namespace Portrino\PxShopware\Domain\Model;

class Customer extends AbstractShopwareModel
{

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $salutation;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var boolean
     */
    protected $active;

    /**
     * @var string
     */
    protected $groupKey;

    /**
     * @param object $raw
     * @param string $token
     */
    public function __construct($raw, $token)
    {
        parent::__construct($raw, $token);

        if (isset($this->raw->email)) {
            $this->email = $this->raw->email;
        }
        if (isset($this->raw->salutation)) {
            $this->salutation = $this->raw->salutation;
        }
        if (isset($this->raw->firstname)) {
            $this->firstName = $this->raw->firstname;
        }
        if (isset($this->raw->lastname)) {
            $this->lastName = $this->raw->lastname;
        }
        if (isset($this->raw->active)) {
            $this->active = $this->raw->active;
        }
        if (isset($this->raw->groupKey)) {
            $this->groupKey = $this->raw->groupKey;
        }
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getGroupKey()
    {
        return $this->groupKey;
    }

}