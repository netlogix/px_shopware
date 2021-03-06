<?php

namespace Portrino\PxShopware\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Andre Wuttig <wuttig@portrino.de>, portrino GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Portrino\PxShopware\Backend\Form\Wizard\SuggestEntryInterface;
use Portrino\PxShopware\Backend\Hooks\ItemEntryInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Category extends AbstractShopwareModel implements SuggestEntryInterface, ItemEntryInterface
{

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var \DateTime $changed
     */
    protected $changed;

    /**
     * @var \TYPO3\CMS\Core\Http\Uri
     */
    protected $uri = '';

    /**
     * @var \Portrino\PxShopware\Domain\Model\Media
     */
    protected $image;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\CategoryClientInterface
     * @inject
     */
    protected $categoryClient;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\MediaClientInterface
     * @inject
     */
    protected $mediaClient;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Category>
     */
    protected $path;

    /**
     * @var int
     */
    protected $parentId;

    /**
     * @var int
     */
    protected $active = false;

    /**
     * @var string
     */
    protected $external;

    /**
     * @var int
     */
    protected $hideTop = false;

    /**
     * @var array
     */
    protected $customerGroups = [];

    /**
     * @var int
     */
    protected $language;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\LanguageToShopwareMappingService
     * @inject
     */
    protected $languageToShopMappingService;

    /**
     * @param object $raw
     * @param string $token
     */
    public function __construct($raw, $token)
    {
        parent::__construct($raw, $token);

        if (isset($this->raw->name)) {
            $this->setName($this->raw->name);
        }
        if (isset($this->raw->pxShopwareUrl)) {
            $this->setUri($this->raw->pxShopwareUrl);
        }
        if (isset($this->raw->changed)) {
            $this->setChanged($this->raw->changed);
        }
        if (isset($this->raw->parentId)) {
            $this->setParentId($this->raw->parentId);
        }
        if (isset($this->raw->active)) {
            $this->setActive($this->raw->active);
        }
        if (isset($this->raw->external)) {
            $this->setExternal($this->raw->external);
        }
        if (isset($this->raw->hideTop)) {
            $this->setHideTop($this->raw->hideTop);
        }
        if (isset($this->raw->customerGroups)) {
            $this->setCustomerGroups($this->raw->customerGroups);
        }

        if ($this->raw->path) {
            if (!$this->languageToShopMappingService) {
                $this->languageToShopMappingService = $this->objectManager->get(\Portrino\PxShopware\Service\Shopware\LanguageToShopwareMappingService::class);
            }
            $this->language = $this->languageToShopMappingService->getSysLanguageUidByParentCategoryPath($this->raw->path);
        }
    }

    public function initializeObject()
    {
        $this->path = new ObjectStorage();
        if (isset($this->getRaw()->media) && is_object($this->getRaw()->media) && isset($this->getRaw()->media->id)) {
            $media = $this->mediaClient->findById($this->getRaw()->media->id);
            $this->setImage($media);
        }
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Category>
     */
    public function getSubCategories()
    {
        return $this->categoryClient->findByParent($this->id);
    }

    /**
     * @return \Portrino\PxShopware\Domain\Model\Category
     */
    public function getParentCategory()
    {
        return $this->categoryClient->findById($this->parentId);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return \TYPO3\CMS\Core\Http\Uri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param \TYPO3\CMS\Core\Http\Uri|string $uri
     */
    public function setUri($uri)
    {
        if (is_string($uri)) {
            $uri = new \TYPO3\CMS\Core\Http\Uri($uri);
        }
        $this->uri = $uri;
    }

    /**
     * @return \DateTime
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * @param \DateTime|string $changed
     */
    public function setChanged($changed)
    {
        if (is_string($changed)) {
            $changed = new \DateTime($changed);
        }
        $this->changed = $changed;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = (int)$parentId;
    }

    /**
     * @return \Portrino\PxShopware\Domain\Model\Media
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param \Portrino\PxShopware\Domain\Model\Media $image
     */
    public function setImage(\Portrino\PxShopware\Domain\Model\Media $image)
    {
        $this->image = $image;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param int $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getExternal()
    {
        return $this->external;
    }

    /**
     * @param string $external
     */
    public function setExternal($external)
    {
        $this->external = $external;
    }

    /**
     * @return int
     */
    public function getHideTop()
    {
        return $this->hideTop;
    }

    /**
     * @param int $hideTop
     */
    public function setHideTop($hideTop)
    {
        $this->hideTop = $hideTop;
    }

    /**
     * @return array
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * @param array $customerGroups
     */
    public function setCustomerGroups($customerGroups)
    {
        $this->customerGroups = $customerGroups;
    }

    /**
     * @return ObjectStorage
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param ObjectStorage $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Adds a path element
     *
     * @param \Portrino\PxShopware\Domain\Model\Category $pathElement
     *
     * @return void
     */
    public function addPathElement(\Portrino\PxShopware\Domain\Model\Category $pathElement)
    {
        $this->path->attach($pathElement);
    }

    /**
     * Removes a path
     *
     * @param \Portrino\PxShopware\Domain\Model\Path $pathElementToRemove The path element to be removed
     *
     * @return void
     */
    public function removePathElement(\Portrino\PxShopware\Domain\Model\Category $pathElementToRemove)
    {
        $this->path->detach($pathElementToRemove);
    }

    /**
     * @param bool $includeSelf TRUE if this element should be included in bread crumb path, FALSE if not
     *
     * @return mixed
     */
    public function getBreadCrumbPath($includeSelf = true)
    {

        if (isset($this->getRaw()->path) && $this->getRaw()->path != '') {
            $pathArray = array_reverse(GeneralUtility::trimExplode('|', $this->getRaw()->path, true));
            foreach ($pathArray as $pathItem) {
                /** @var Category|NULL $pathElement */
                $pathElement = $this->categoryClient->findById($pathItem);
                if ($pathElement) {
                    $this->addPathElement($pathElement);
                }
            }
        }

        /** @var array $path */
        $path = array_map(function ($item) {
            return $item->getName();
        }, $this->getPath()->toArray());
        if ($includeSelf === true) {
            array_push($path, $this->getName());
        }
        return implode('/', $path);
    }

    /**
     * @return int
     */
    public function getSelectItemId()
    {
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getSelectItemLabel()
    {
        return $this->getName() . ' [' . $this->getId() . ']';
    }

    /**
     * @return int
     */
    public function getSuggestId()
    {
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getSuggestLabel()
    {
        return $this->getName() . ' [' . $this->getId() . ']';
    }

    /**
     * @return string
     */
    public function getSuggestDescription()
    {
        return $this->getBreadCrumbPath(false);
    }

    /**
     * @return string
     */
    public function getSuggestIconIdentifier()
    {
        return 'px-shopware-category';
    }

    /**
     * @return int
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param int $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

}