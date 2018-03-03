<?php
namespace Portrino\PxShopware\Service\Shopware;

use Portrino\PxShopware\Domain\Model\Customer;

interface CustomerClientInterface extends AbstractShopwareApiClientInterface
{

    const ENDPOINT = 'customers';
    const CACHE_TAG = 'showpare_customer';
    const ENTITY_CLASS_NAME = Customer::class;

}
