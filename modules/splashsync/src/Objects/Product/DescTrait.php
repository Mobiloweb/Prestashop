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
use Language;
use Product;
use Splash\Core\SplashCore as Splash;
use Splash\Local\Services\LanguagesManager;
use Splash\Local\Services\MultiShopManager as MSM;
use Translate;

/**
 * Access to Product Descriptions Fields
 */
trait DescTrait
{
    //====================================================================//
    //  Multilanguage Fields
    //====================================================================//

    /**
     * Build Description Fields using FieldFactory
     *
     * @return void
     */
    protected function buildDescFields()
    {
        $groupName = Translate::getAdminTranslation("Information", "AdminProducts");
        $this->fieldsFactory()->setDefaultLanguage(LanguagesManager::getDefaultLanguage());

        //====================================================================//
        // PRODUCT DESCRIPTIONS
        //====================================================================//

        foreach (LanguagesManager::getAvailableLanguages() as $isoLang) {
            //====================================================================//
            // Name without Options
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("name")
                ->Name($this->spl->l("Product Name without Options"))
                ->Group($groupName)
                ->MicroData("http://schema.org/Product", "alternateName")
                ->setMultilang($isoLang)
                ->addOption("shop", MSM::MODE_ALL)
                ->isRequired(LanguagesManager::isDefaultLanguage($isoLang));

            //====================================================================//
            // Name with Options
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("fullname")
                ->Name($this->spl->l("Product Name with Options"))
                ->Group($groupName)
                ->MicroData("http://schema.org/Product", "name")
                ->setMultilang($isoLang)
                ->isListed(LanguagesManager::isDefaultLanguage($isoLang))
                ->isReadOnly();

            //====================================================================//
            // Long Description
            $this->fieldsFactory()->create(SPL_T_TEXT)
                ->Identifier("description")
                ->Name(Translate::getAdminTranslation("description", "AdminProducts"))
                ->Group($groupName)
                ->MicroData("http://schema.org/Article", "articleBody")
                ->setMultilang($isoLang);

            //====================================================================//
            // Short Description
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("description_short")
                ->Name(Translate::getAdminTranslation("Short Description", "AdminProducts"))
                ->Group($groupName)
                ->MicroData("http://schema.org/Product", "description")
                ->setMultilang($isoLang);
        }
    }

    /**
     * Read requested Field
     *
     * @param string $key Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getDescFields($key, $fieldName)
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
                case 'name':
                case 'description':
                case 'description_short':
                    $this->out[$fieldName] = $this->getMultilang($baseFieldName, $idLang);
                    unset($this->in[$key]);

                    break;
                case 'fullname':
                    //====================================================================//
                    // Product Specific - Read Full Product Name with Attribute Description
                    Splash::log()->www(__METHOD__, "FULLNAME");
                    $this->out[$fieldName] = Product::getProductName($this->object->id, $this->AttributeId, $idLang);
                    unset($this->in[$key]);

                    break;
            }
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
    protected function setDescFields($fieldName, $fieldData)
    {
        //====================================================================//prod
        // Walk on Available Languages
        foreach (LanguagesManager::getAvailableLanguages() as $idLang => $isoLang) {
            //====================================================================//
            // Decode Multilang Field Name
            $baseFieldName = LanguagesManager::fieldNameDecode($fieldName, $isoLang);
            //====================================================================//
            // WRITE Field
            switch ($baseFieldName) {
                case 'name':
                    $this->setMultilang($baseFieldName, $idLang, $fieldData);
                    $this->addMsfUpdateFields("Product", $baseFieldName, $idLang);

                    /* MBW - Add seller name on update */
                    if (Configuration::get('SPLASHMBW_ENABLE_JAMARKETPLACE')) {
                        foreach ($this->object->{$baseFieldName} as &$name) {
                            $name .= ' - '.$this->in['ja_shop_name'];
                        }
                    }

                    unset($this->in[$fieldName]);

                    break;
                case 'description':
                case 'description_short':
                    //====================================================================//
                    // MBW - Set description and description_short only if they are empty
                    if (Configuration::get('SPLASHMBW_DESC_BEHAVIOR')) {
                        try {
                            $psProduct = new Product((int)$this->ProductId);
                            if (strlen($psProduct->description_short[$idLang]) < 1) {
                                $maxLength = (int)Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT');
                                $this->setMultilang(
                                    $baseFieldName,
                                    $idLang,
                                    $fieldData,
                                    $maxLength ? $maxLength : null
                                );
                                $this->addMsfUpdateFields("Product", $baseFieldName, $idLang);
                            }
                        } catch (\Exception $e) {
                            Splash::log()->err('Cannot load product n°'.$this->ProductId);
                        }
                    } else {
                        $maxLength = (int)Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT');
                        $this->setMultilang($baseFieldName, $idLang, $fieldData, $maxLength ? $maxLength : null);
                        $this->addMsfUpdateFields("Product", $baseFieldName, $idLang);
                    }

                    //====================================================================//
                    unset($this->in[$fieldName]);

                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Read Multilangual Fields of an Object
     *
     * @param Product $object Pointer to Prestashop Object
     *
     * @return array
     */
    private function getMultilangTags(&$object)
    {
        //====================================================================//
        // Native Multilangs Descriptions
        $languages = Language::getLanguages();
        if (empty($languages)) {
            return array();
        }

        //====================================================================//
        // For Each Available Language
        $data = array();
        foreach ($languages as $lang) {
            //====================================================================//
            // Encode Language Code From Splash Format to Prestashop Format (fr_FR => fr-fr)
            $langCode = LanguagesManager::langEncode($lang["language_code"]);
            $langId = (int)$lang["id_lang"];
            //====================================================================//
            // Product Specific - Read Meta Keywords
            $data[$langCode] = $object->getTags($langId);
        }

        return $data;
    }
}
