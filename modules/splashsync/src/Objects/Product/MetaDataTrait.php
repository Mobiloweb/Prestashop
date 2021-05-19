<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Product;

use Configuration;
use Splash\Client\Splash;
use Splash\Local\Services\LanguagesManager;
use Translate;

/**
 * Access to Product Meta Data Fields
 */
trait MetaDataTrait
{
    //====================================================================//
    //  Multilanguage Metadata Fields
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildMetaDataFields()
    {
        $groupName = Translate::getAdminTranslation("Information", "AdminProducts");
        $this->fieldsFactory()->setDefaultLanguage(LanguagesManager::getDefaultLanguage());

        //====================================================================//
        // PRODUCT METADATA
        //====================================================================//

        foreach (LanguagesManager::getAvailableLanguages() as $isoLang) {
            //====================================================================//
            // Meta Description
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("meta_description")
                ->Name(Translate::getAdminTranslation("Meta description", "AdminProducts"))
                ->Description($groupName . " " . Translate::getAdminTranslation("Meta description", "AdminProducts"))
                ->Group($groupName)
                ->MicroData("http://schema.org/Article", "headline")
                ->setMultilang($isoLang);

            //====================================================================//
            // Meta Title
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("meta_title")
                ->Name(Translate::getAdminTranslation("Meta title", "AdminProducts"))
                ->Description($groupName . " " . Translate::getAdminTranslation("Meta title", "AdminProducts"))
                ->Group($groupName)
                ->MicroData("http://schema.org/Article", "name")
                ->setMultilang($isoLang);

            //====================================================================//
            // Meta KeyWords
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("meta_keywords")
                ->Name(Translate::getAdminTranslation("Meta keywords", "AdminProducts"))
                ->Description($groupName . " " . Translate::getAdminTranslation("Meta keywords", "AdminProducts"))
                ->Group($groupName)
                ->MicroData("http://schema.org/Article", "keywords")
                ->setMultilang($isoLang)
                ->isReadOnly();

            //====================================================================//
            // Rewrite Url
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("link_rewrite")
                ->Name(Translate::getAdminTranslation("Friendly URL", "AdminProducts"))
                ->Description($groupName . " " . Translate::getAdminTranslation("Friendly URL", "AdminProducts"))
                ->Group($groupName)
                ->MicroData("http://schema.org/Product", "urlRewrite")
                ->setMultilang($isoLang);
        }

        //====================================================================//
        // MBW - Create ja_shop_name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("ja_shop_name")
            ->Name(Translate::getAdminTranslation("Shop name", "AdminShopparametersFeature"))
            ->Description($groupName . " " . Translate::getAdminTranslation("Shop name", "AdminProducts"))
			->MicroData("http://schema.org/Product", "vendorName")
            ->Group($groupName);
    }

    /**
     * Read requested Field
     *
     * @param string $key Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getMetaDataFields($key, $fieldName)
    {
        //====================================================================//
        // Walk on Available Languages
        foreach (LanguagesManager::getAvailableLanguages() as $idLang => $isoLang) {
            //====================================================================//
            // Decode Multilang Field Name
            $baseFieldName = LanguagesManager::fieldNameDecode($fieldName, $isoLang);
            //====================================================================//
            // READ Fields
            switch ($baseFieldName) {
                case 'link_rewrite':
                case 'meta_description':
                case 'meta_title':
                    $this->out[$fieldName] = $this->getMultilang($baseFieldName, $idLang);
                    unset($this->in[$key]);

                    break;
                case 'meta_keywords':
                    //====================================================================//
                    // Product Specific - Read Meta Keywords
                    $this->out[$fieldName] = $this->object->getTags($idLang);
                    unset($this->in[$key]);

                    break;

            }
        }

        if ($fieldName == 'ja_shop_name') {
            // MBW - Get ja_shop_name
            $this->getSimple($fieldName);
            unset($this->in[$key]);
        }
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed $fieldData Field Data
     *
     * @return void
     */
    protected function setMetaDataFields($fieldName, $fieldData)
    {
        //====================================================================//
        // Walk on Available Languages
        foreach (LanguagesManager::getAvailableLanguages() as $idLang => $isoLang) {
            //====================================================================//
            // Decode Multilang Field Name
            $baseFieldName = LanguagesManager::fieldNameDecode($fieldName, $isoLang);
            //====================================================================//
            // WRITE Field
            switch ($baseFieldName) {
                case 'link_rewrite':
                    $this->setMultilang($baseFieldName, $idLang, $fieldData);
                    $this->addMsfUpdateFields("Product", $baseFieldName, $idLang);
                    unset($this->in[$fieldName]);

                    break;

                case 'meta_description':

                    if (Configuration::get('SPLASHMBW_DESC_BEHAVIOR')) {

                        $incoming = $fieldData;
                        $current = $this->object->$baseFieldName[$idLang];

                        $output = '';

                        /* If the DESC_BEHAVIOR is enabled then synchronize the meta_description only if it's empty on
                        the marketplace and if it's not empty on the sender server */
                        if (empty($current) && !empty($incoming)) {
                            $output = $incoming;
                            $this->setMultilang($baseFieldName, $idLang, $output);
                        }

                    } else {
                        $this->setMultilang($baseFieldName, $idLang, $fieldData);
                    }

                    $this->addMsfUpdateFields("Product", $baseFieldName, $idLang);

                    unset($this->in[$fieldName]);
                    break;

                case 'meta_title':

                    if (Configuration::get('SPLASHMBW_DESC_BEHAVIOR')) {

                        $incoming = $fieldData;
                        $current = $this->object->$baseFieldName[$idLang];

                        /* If DESC_BEHAVIOR isn't enabled, keep the default behavior (synchronize the meta_description) */
                        $output = '';

                        if (empty($current)) {

                            /* If the DESC_BEHAVIOR is enabled then synchronize the meta_title only if it's empty on
                            the marketplace and if it's not empty on the sender server */
                            if (!empty($incoming)) {
                                $output = $incoming;
                            }

                            /* Auto generate the meta_title and truncate it if the meta_title from the marketplace and
                            the sender server are both empty, and if the DESC_BEHAVIOR is enabled */
                            if (empty($incoming)) {

                                $maxLength = 60;
                                $toAdd = 'vendu par';

                                $arrTitle = explode('-', $this->object->name[$idLang]);

                                $shopName = array_values(array_slice($arrTitle, -1))[0];

                                array_pop($arrTitle);

                                $newObjectName = implode(' - ', $arrTitle);

                                $totalLength = (strlen($newObjectName) + strlen($toAdd) + strlen($shopName));

                                if ($totalLength > $maxLength) {
                                    $excess = $totalLength - $maxLength;
                                    $newObjectName = substr($newObjectName, 0, (strlen($newObjectName) - $excess) - 3) . '.. ';
                                }

                                $output = trim($newObjectName) . ' ' . trim($toAdd) . ' ' . trim($shopName);
                            }

                            $this->setMultilang($baseFieldName, $idLang, $output);
                        }

                        $this->addMsfUpdateFields("Product", $baseFieldName, $idLang);

                    } else {

                        $this->setMultilang($baseFieldName, $idLang, $fieldData);
                        $this->addMsfUpdateFields("Product", $baseFieldName, $idLang);
                    }

                    unset($this->in[$fieldName]);
                    break;
                default:
                    break;
            }
        }

        // MBW - Set ja_shop_name
        if ($fieldName == 'ja_shop_name') {
            if (empty($this->object->ja_shop_name)) {
                $this->setSimple($fieldName, $fieldData);
            } else {
                $this->setSimple($fieldName, $this->object->ja_shop_name);
            }

            unset($this->in[$fieldName]);
        }
    }
}
