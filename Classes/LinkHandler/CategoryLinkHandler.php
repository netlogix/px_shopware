<?php

namespace Portrino\PxShopware\LinkHandler;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Sascha Nowak <sascha.nowak@netlogix.de>, netlogix GmbH & Co. KG
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

use Portrino\PxShopware\Domain\Model\Category;
use Portrino\PxShopware\Service\Shopware\CategoryClientInterface;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class CategoryLinkHandler extends AbstractLinkHandler
{

    /**
     * @var integer
     */
    protected $titleLen;

    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->client = $objectManager->get(CategoryClientInterface::class);
        $this->type = 'category';
    }

    /**
     * @param ObjectStorage<\Portrino\PxShopware\Domain\Model\ShopwareModelInterface> $objects
     * @return string
     */
    protected function renderContent(ObjectStorage $objects)
    {
        $this->titleLen = (int)$this->getBackendUser()->uc['titleLen'];
        $selectedId = $this->object ? $this->object->getId() : 0;

        $categories = array_map(function ($category) use ($selectedId) {
            /** @var Category $category */
            return [
              'id' => (int)$category->getId(),
              'label' => $category->getSelectItemLabel(),
              'selected' => $selectedId === $category->getId(),
              'parentId' => (int)$category->getParentId(),
              'children' => [],
            ];
        }, $objects->toArray());

        $categoryTree = $this->buildTree($categories);

        $content = '<ul class="list-tree list-tree-root">';
        foreach ($categoryTree as $category) {
            $icon = '<span title="' . htmlspecialchars($category['label']) . '">'
                . $this->iconFactory->getIcon('apps-pagetree-root', Icon::SIZE_SMALL)
                . '</span>';

            $content .= '
<li class="list-tree-control-open">
    <span class="list-tree-group">
        <span class="list-tree-icon">' . $icon . '</span>
        <span class="list-tree-title">' . htmlspecialchars(GeneralUtility::fixed_lgd_cs($category['label'], $this->titleLen)) . '</span>
        ' . $this->renderChildren($category['children'], 'apps-pagetree-page-domain') . '
    </span>
</li>';
        }
        $content .= '</ul>';
        return $content;
    }

    /**
     * @param array $categories
     * @return string
     */
    private function renderChildren($categories, $iconIdentifier)
    {
        if (count($categories) === 0) {
            return '';
        }

        $content = '<ul class="list-tree">';
        foreach ($categories as $category) {
            $selected = $category['selected'] ? ' class="active"' : '';
            $icon = '<span title="' . htmlspecialchars($category['label']) . '">'
                . $this->iconFactory->getIcon($iconIdentifier, Icon::SIZE_SMALL)
                . '</span>';
            $content .=
'<li' . $selected . '>
    <span class="list-tree-group">
        <a href="#" class="t3js-fileLink list-tree-group" title="' . htmlspecialchars($category['label']) . '" data-' . $this->type . '="' . $this->getPrefix() . htmlspecialchars($category['id']) . '">
            <span class="list-tree-icon">' . $icon . '</span>
            <span class="list-tree-title">' . htmlspecialchars(GeneralUtility::fixed_lgd_cs($category['label'], $this->titleLen)) . '</span>
        </a>
    </span>
   ' . $this->renderChildren($category['children'], 'px-shopware-' . $this->type) . '
</li>';
        }

        $content .= '</ul>';
        return $content;
    }

    /**
     * @param array $elements
     * @param int $parentId
     * @return array
     */
    private function buildTree(array &$elements, $parentId = 0)
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['parentId'] === $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

}