<?php

namespace Portrino\PxShopware\Domain\Model;

interface ShopwareModelInterface
{

    /**
     * @return string
     */
    public function getId();

    /**
     * @param string $id
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getRaw();

    /**
     * @param string $raw
     */
    public function setRaw($raw);

    /**
     * @return boolean
     */
    public function isToken();

    /**
     * @param boolean $token
     */
    public function setToken($token);

}