[![N|Solid](https://github.com/SplashSync/Php-Core/raw/master/img/github.jpg)](https://www.splashsync.com)

# Splash Sync Module for PrestaShop
Splash Php Module for Prestashop E-Commerce Platforms.

This module implement Splash Sync connector for Prestashop. 
It provides access to multiples Objects for automated synchronization though Splash Sync dedicated protocol.

[![Build Status](https://travis-ci.org/SplashSync/Prestashop.svg?branch=master)](https://travis-ci.org/SplashSync/Prestashop)
[![Latest Stable Version](https://poser.pugx.org/splash/prestashop/v/stable)](https://packagist.org/packages/splash/prestashop)
[![Latest Unstable Version](https://poser.pugx.org/splash/prestashop/v/unstable)](https://packagist.org/packages/splash/prestashop)
[![License](https://poser.pugx.org/splash/prestashop/license)](https://packagist.org/packages/splash/prestashop)

## Installation

* Download the Mobiloweb version [here](https://github.com/Mobiloweb/Prestashop/releases/tag/1.0)
* Copy module contents on PrestaShop module's folder (modules/splashsync) 
* Enable & Configure the module from Administration Module's page.
* Set the `PS_PRODUCT_SHORT_DESC_LIMIT` column to **2000** in the `ps_configuration` table
  
```sql
UPDATE `ps_configuration`
SET value = "2000"
WHERE name = "PS_PRODUCT_SHORT_DESC_LIMIT";
```

## JAMarketplace support

If enabled, you'll need to add the following in **classes/Product.php** (receiver only):

`public $ja_shop_name;` at line ~230

`'ja_shop_name' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],` in the fields at line 324

## Custom configuration by Mobiloweb

![Configuration](https://i.imgur.com/DIfWfVs.png)

## Requirements

* PHP 7.2+
* PrestaShop 1.6+
* An active Splash Sync User Account

## Documentation

For the configuration guide and reference, see: [Prestashop Module Documentation](https://splashsync.gitlab.io/Prestashop/)

## Contributing

Any Pull requests are welcome! 

This module is part of [SplashSync](http://www.splashsync.com) project.

