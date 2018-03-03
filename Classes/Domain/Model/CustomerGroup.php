<?php

namespace Portrino\PxShopware\Domain\Model;

class CustomerGroup extends AbstractShopwareModel
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $key;

    /**
     * @param object $raw
     * @param string $token
     */
    public function __construct($raw, $token)
    {
        parent::__construct($raw, $token);

        if (isset($this->raw->name)) {
            $this->name = $this->raw->name;
        }

        if (isset($this->raw->key)) {
            $this->key = $this->raw->key;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

}