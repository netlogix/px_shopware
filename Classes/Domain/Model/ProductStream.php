<?php

namespace Portrino\PxShopware\Domain\Model;

use Portrino\PxShopware\Backend\Form\Wizard\SuggestEntryInterface;
use Portrino\PxShopware\Backend\Hooks\ItemEntryInterface;

class ProductStream extends AbstractShopwareModel implements SuggestEntryInterface, ItemEntryInterface
{

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @inheritdoc
     */
    public function __construct($raw, $token = '')
    {
        parent::__construct($raw, $token);

        if (isset($this->raw->name)) {
            $this->name = $this->raw->name;
        }

        if (isset($this->raw->description)) {
            $this->description = $this->raw->description;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getSuggestLabel()
    {
        return '[' . $this->getId() . '] ' . $this->getName();
    }

    /**
     * @return string
     */
    public function getSuggestDescription()
    {
        return $this->getDescription();
    }

    /**
     * @return int
     */
    public function getSelectItemId()
    {
        return (int)$this->getId();
    }

    /**
     * @return string
     */
    public function getSelectItemLabel()
    {
        return '[' . $this->getId() . '] ' . $this->getName();
    }

    /**
     * @return int
     */
    public function getSuggestId()
    {
        return (int)$this->getId();
    }

    /**
     * @return string
     */
    public function getSuggestIconIdentifier()
    {
        return '';
    }
}