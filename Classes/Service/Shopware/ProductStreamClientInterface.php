<?php
namespace Portrino\PxShopware\Service\Shopware;
use Portrino\PxShopware\Domain\Model\ProductStream;

interface ProductStreamClientInterface extends AbstractShopwareApiClientInterface
{

    const ENDPOINT = 'productStream';
    const CACHE_TAG = 'showpare_product_stream';
    const ENTITY_CLASS_NAME = ProductStream::class;

    /**
     * @param $id
     * @param bool $doCacheRequest
     * @param array $params
     *
     * @return \Portrino\PxShopware\Domain\Model\ProductStream
     */
    public function findById($id, $doCacheRequest = TRUE, $params = []);

}
