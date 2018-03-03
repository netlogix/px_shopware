<?php
namespace Portrino\PxShopware\Service\Shopware;

use Portrino\PxShopware\Domain\Model\CustomerGroup;

interface CustomerGroupClientInterface extends AbstractShopwareApiClientInterface
{

    const ENDPOINT = 'customerGroups';
    const CACHE_TAG = 'showpare_customer_group';
    const ENTITY_CLASS_NAME = CustomerGroup::class;

}
