<?php
namespace Portrino\PxShopware\Service\Shopware;

class ProductStreamClient extends AbstractShopwareApiClient implements ProductStreamClientInterface
{

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return self::ENDPOINT;
    }

    /**
     * @return string
     */
    public function getEntityClassName()
    {
        return self::ENTITY_CLASS_NAME;
    }

}
