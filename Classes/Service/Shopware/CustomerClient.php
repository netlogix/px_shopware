<?php
namespace Portrino\PxShopware\Service\Shopware;

class CustomerClient extends AbstractShopwareApiClient implements CustomerClientInterface
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
