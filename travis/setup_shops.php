<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

require_once dirname(__DIR__) . "/modules/splashsync/vendor/autoload.php";
require_once dirname(__DIR__) . "/vendor/autoload.php";

use Splash\Local\Services\MultiShopManager as MSM;

//====================================================================//
// Init Splash for Local Includes
Splash\Client\Splash::core();
Splash\Client\Splash::local();

//====================================================================//
// Ensure Number of Active Shops
if (count(MSM::getShopIds()) < 2) {
    var_dump(MSM::addPhpUnitShop("Phpunit1"));
}

var_dump(MSM::isFeatureActive());

//====================================================================//
// Setup Shops Context for Testing
$options = getopt("s:");
if (MSM::isFeatureActive() && isset($options["s"]) && is_numeric($options["s"])) {
    $shopId = intval($options["s"]);
    MSM::setContext();
    Configuration::updateValue('SPLASH_MSF_FOCUSED', $shopId ? $shopId : false);
    print_r("Setuped for ".($shopId ? "Shop ".$shopId : "All Shops").PHP_EOL);
}
