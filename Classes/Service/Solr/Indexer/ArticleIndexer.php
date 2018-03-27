<?php
namespace Portrino\PxShopware\Service\Solr\Indexer;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Thomas Griessbach <griessbach@portrino.de>, portrino GmbH
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

use Portrino\PxShopware\Domain\Model\AbstractShopwareModel;
use Portrino\PxShopware\Domain\Model\Article;
use Portrino\PxShopware\Domain\Model\Category;
use Portrino\PxShopware\Domain\Model\Detail;
use Portrino\PxShopware\Service\Shopware\ArticleClientInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ArticleIndexer
 *
 * @package Portrino\PxShopware\Service\Solr\Indexer
 */
class ArticleIndexer extends AbstractShopwareIndexer
{

    /**
     * @var string
     */
    protected $clientClassName = ArticleClientInterface::class;

    /**
     * check if record should be added/updated or deleted from index
     *
     * @param AbstractShopwareModel $article The item to index
     * @return bool valid or not
     */
    protected function itemIsValid(AbstractShopwareModel $article)
    {
        if (!$article instanceof Article) {
            return false;
        }

        $result = parent::itemIsValid($article);

        // ALSO check for categories, if article has no category shopware will throw error! so do not add to search result!
        if (!isset($article->getRaw()->categories) || $article->getRaw()->categories == false) {
            $result = false;
        }

        return $result;
    }

    /**
     * overwrite special fields for articles
     *
     * @param \Apache_Solr_Document $itemDocument
     * @param AbstractShopwareModel $article
     * @param integer $language The language to use.
     * @return \Apache_Solr_Document $itemDocument
     */
    protected function overwriteSpecialFields(\Apache_Solr_Document $itemDocument, AbstractShopwareModel $article, $language = 0)
    {
        if (!$article instanceof Article) {
            return $itemDocument;
        }

        $itemDocument->setField('title', $article->getName());

        if ($article->getRaw()->keywords) {
            $itemDocument->setField('keywords', GeneralUtility::trimExplode(',', $article->getRaw()->keywords, true));
        }

        if ($article->getChanged()->getTimestamp()) {
            $itemDocument->setField('changed', $article->getChanged()->getTimestamp());
        }

        $itemDocument->setField('description', trim(strip_tags($article->getDescription())));
        $itemDocument->setField('descriptionLong_textS', trim(strip_tags($article->getDescriptionLong())));

        if ($article->getFirstImage()) {
            $itemDocument->setField('image_stringS', $article->getFirstImage()->getUrl());
        }

        if ($article->getCategories()->count() > 0) {
            $categoryNames = [];
            /** @var Category $category */
            foreach ($article->getCategories() as $category) {
                if ($category->getLanguage() == $language) {
                    $categoryNames[] = $category->getName();
                }
            }

            $itemDocument->setField('category_stringM', array_unique($categoryNames));
            $itemDocument->setField('category_textM', array_unique($categoryNames));
        }

        if ($article->getDetails()->count() > 0) {
            $detailLabels = [];
            /** @var Detail $detail */
            foreach ($article->getDetails() as $detail) {
                $detailLabels[] = $detail->getNumber() . ' (' . $detail->getAdditionalText() . ')';
            }

            $itemDocument->setField('details_stringM', array_unique($detailLabels));
            $itemDocument->setField('details_textM', array_unique($detailLabels));
        }

        if (is_object($article->getRaw()) && is_object($article->getRaw()->tax)) {
            $itemDocument->setField('tax_doubleS', $article->getRaw()->tax->tax);
            $itemDocument->setField('taxName_stringS', $article->getRaw()->tax->name);
        }

        if (is_object($article->getRaw()) && is_object($article->getRaw()->mainDetail)) {
            if ($article->getRaw()->mainDetail->number) {
                $itemDocument->setField('productNumber_stringS', $article->getRaw()->mainDetail->number);
                $itemDocument->setField('productNumber_textS', $article->getRaw()->mainDetail->number);
            }
            if ($article->getRaw()->mainDetail->ean) {
                $itemDocument->setField('ean_textS', $article->getRaw()->mainDetail->ean);
            }
            if ($article->getRaw()->mainDetail->additionalText) {
                $itemDocument->setField('additionalText_textS', $article->getRaw()->mainDetail->additionalText);
            }
            if ($article->getRaw()->mainDetail->unitId) {
                $itemDocument->setField('unitId_stringS', $article->getRaw()->mainDetail->unitId);
            }
            if ($article->getRaw()->mainDetail->packUnit) {
                $itemDocument->setField('packUnit_stringS', $article->getRaw()->mainDetail->packUnit);
            }
            if ($article->getRaw()->mainDetail->purchaseUnit) {
                $itemDocument->setField('purchaseUnit_tdoubleS', $article->getRaw()->mainDetail->purchaseUnit);
            }
            if ($article->getRaw()->mainDetail->referenceUnit) {
                $itemDocument->setField('referenceUnit_tdoubleS', $article->getRaw()->mainDetail->referenceUnit);
            }

            if (is_array($article->getRaw()->mainDetail->prices) && count($article->getRaw()->mainDetail->prices) > 0) {
                $itemDocument->setField('price_tDoubleS', $article->getRaw()->mainDetail->prices[0]->price);
                $itemDocument->setField('pseudoPrice_tDoubleS', $article->getRaw()->mainDetail->prices[0]->pseudoPrice);
            }
        }
        if (is_object($article->getRaw()->supplier)) {
            $itemDocument->setField('supplier_stringS', $article->getRaw()->supplier->name);
            $itemDocument->setField('supplier_textS', $article->getRaw()->supplier->name);
        }

        return $itemDocument;
    }

}