<?php

namespace Portrino\PxShopware\Domain\Model;

class Command implements \JsonSerializable
{

    const ACTION_CREATE = 'create';
    const ACTION_MOVE = 'move';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    const TYPE_ARTICLE = 'article';
    const TYPE_CATEGORY = 'category';
    const TYPE_MEDIA = 'media';
    const TYPE_SHOP = 'shop';
    const TYPE_VERSION = 'version';
    const TYPE_CUSTOMER = 'customer';
    const TYPE_CUSTOMER_GROUP = 'customergroup';
    const TYPE_PRODUCT_STREAM = 'productstream';

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $action;

    /**
     * @param integer $id
     * @param string $type
     * @param string $action
     */
    public function __construct($id, $type, $action)
    {
        $this->id = (int)$id;
        $this->type = (string)$type;
        $this->action = (string)$action;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'action' => $this->action,
        ];
    }

    /**
     * @param string $json
     * @return Command
     */
    public static function fromJson($json)
    {
        return self::fromArray(json_decode($json, true));
    }

    /**
     * @param array $data
     * @return Command
     */
    public static function fromArray($data)
    {
        return new self($data['id'], $data['type'], $data['action']);
    }
}