<?php

namespace Portrino\PxShopware\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\TypoScriptService;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

class Util
{

    /**
     * @param int $pageId
     * @param int $language
     * @return array
     */
    public static function getConfigurationFromPageId($pageId, $language)
    {
        // If we're on UID 0, we cannot retrieve a configuration currently.
        // getRootline() below throws an exception (since #typo3-60 )
        // as UID 0 cannot have any parent rootline by design.
        if ($pageId == 0) {
            return [];
        }

        $cacheId = md5($pageId . '|' . $language);
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cache = $cacheManager->getCache('px_shopware_configuration');
        $configuration = $cache->get($cacheId);

        if (!$configuration) {
            if (!isset($GLOBALS['TSFE'])) {
                self::initializeTsfe($pageId, $language);
            }
            $configuration = self::getConfigurationFromInitializedTSFE('plugin.tx_pxshopware.settings');
            $configuration = GeneralUtility::makeInstance(TypoScriptService::class)->convertTypoScriptArrayToPlainArray($configuration);
            $cache->set($cacheId, $configuration);
        }

        return $configuration;
    }

    /**
     * Determines the rootpage ID for a given page.
     *
     * @param integer $pageId A page ID somewhere in a tree.
     * @param bool $forceFallback Force the explicit detection and do not use the current frontend root line
     * @return integer The page's tree branch's root page ID
     */
    public static function getRootPageId($pageId = 0, $forceFallback = false)
    {
        $rootLine = array();
        $rootPageId = intval($pageId) ? intval($pageId) : $GLOBALS['TSFE']->id;

        // frontend
        if (!empty($GLOBALS['TSFE']->rootLine)) {
            $rootLine = $GLOBALS['TSFE']->rootLine;
        }

        // fallback, backend
        if ($pageId != 0 && ($forceFallback || empty($rootLine) || !self::rootlineContainsRootPage($rootLine))) {
            $pageSelect = GeneralUtility::makeInstance(PageRepository::class);
            $rootLine = $pageSelect->getRootLine($pageId, '', true);
        }

        $rootLine = array_reverse($rootLine);
        foreach ($rootLine as $page) {
            if ($page['is_siteroot']) {
                $rootPageId = $page['uid'];
            }
        }

        return $rootPageId;
    }

    /**
     * Checks whether a given root line contains a page marked as root page.
     *
     * @param array $rootLine A root line array of page records
     * @return boolean TRUE if the root line contains a root page record, FALSE otherwise
     */
    private static function rootlineContainsRootPage(array $rootLine)
    {
        $containsRootPage = false;

        foreach ($rootLine as $page) {
            if ($page['is_siteroot']) {
                $containsRootPage = true;
                break;
            }
        }

        return $containsRootPage;
    }

    /**
     * This function is used to retrieve the configuration from a previous initialized TSFE
     * (see: getConfigurationFromPageId)
     *
     * @param string $path
     * @return mixed
     */
    private static function getConfigurationFromInitializedTSFE($path)
    {
        $tmpl = GeneralUtility::makeInstance(ExtendedTemplateService::class);
        $configuration = $tmpl->ext_getSetup($GLOBALS['TSFE']->tmpl->setup, $path);
        $configurationToUse = $configuration[0];
        return $configurationToUse;
    }

    /**
     * Initializes the TSFE for a given page ID and language.
     *
     * @param integer $pageId The page id to initialize the TSFE for
     * @param integer $language System language uid, optional, defaults to 0
     * @param boolean $useCache Use cache to reuse TSFE
     * @return void
     */
    private static function initializeTsfe($pageId, $language = 0, $useCache = true)
    {
        static $tsfeCache = array();

        // resetting, a TSFE instance with data from a different page Id could be set already
        unset($GLOBALS['TSFE']);

        $cacheId = $pageId . '|' . $language;

        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = GeneralUtility::makeInstance(NullTimeTracker::class);
        }

        if (!isset($tsfeCache[$cacheId]) || !$useCache) {
            GeneralUtility::_GETset($language, 'L');

            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(TypoScriptFrontendController::class, $GLOBALS['TYPO3_CONF_VARS'], $pageId, 0);

            // for certain situations we need to trick TSFE into granting us
            // access to the page in any case to make getPageAndRootline() work
            // see http://forge.typo3.org/issues/42122
            $pageRecord = BackendUtility::getRecord('pages', $pageId);
            $groupListBackup = $GLOBALS['TSFE']->gr_list;
            $GLOBALS['TSFE']->gr_list = $pageRecord['fe_group'];

            $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(PageRepository::class);
            $GLOBALS['TSFE']->getPageAndRootline();

            // restore gr_list
            $GLOBALS['TSFE']->gr_list = $groupListBackup;

            $GLOBALS['TSFE']->initTemplate();
            $GLOBALS['TSFE']->forceTemplateParsing = true;
            $GLOBALS['TSFE']->initFEuser();
            $GLOBALS['TSFE']->initUserGroups();
            // $GLOBALS['TSFE']->getCompressedTCarray(); // seems to cause conflicts sometimes

            $GLOBALS['TSFE']->no_cache = true;
            $GLOBALS['TSFE']->tmpl->start($GLOBALS['TSFE']->rootLine);
            $GLOBALS['TSFE']->no_cache = false;
            $GLOBALS['TSFE']->getConfigArray();

            $GLOBALS['TSFE']->settingLanguage();
            if (!$useCache) {
                $GLOBALS['TSFE']->settingLocale();
            }

            $GLOBALS['TSFE']->newCObj();
            $GLOBALS['TSFE']->absRefPrefix = self::getAbsRefPrefixFromTSFE($GLOBALS['TSFE']);
            $GLOBALS['TSFE']->calculateLinkVars();

            if ($useCache) {
                $tsfeCache[$cacheId] = $GLOBALS['TSFE'];
            }
        }

        if ($useCache) {
            $GLOBALS['TSFE'] = $tsfeCache[$cacheId];
            $GLOBALS['TSFE']->settingLocale();
        }
    }

    /**
     * Resolves the configured absRefPrefix to a valid value and resolved if absRefPrefix
     * is set to "auto".
     *
     * @param TypoScriptFrontendController $TSFE
     * @return string
     */
    private static function getAbsRefPrefixFromTSFE(TypoScriptFrontendController $TSFE)
    {
        $absRefPrefix = '';
        if (empty($TSFE->config['config']['absRefPrefix'])) {
            return $absRefPrefix;
        }

        $absRefPrefix = trim($TSFE->config['config']['absRefPrefix']);
        if ($absRefPrefix === 'auto') {
            $absRefPrefix = GeneralUtility::getIndpEnv('TYPO3_SITE_PATH');
        }

        return $absRefPrefix;
    }

}