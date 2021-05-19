[![N|Solid](https://github.com/SplashSync/Php-Core/raw/master/img/github.jpg)](https://www.splashsync.com)

<img align="center" width="100" height="100" src="https://user-images.githubusercontent.com/17336553/116259806-6c4e9180-a776-11eb-8157-b74f50baf166.png">  - Mobiloweb customized version

# Splash Sync Module for PrestaShop
Splash Php Module for Prestashop E-Commerce Platforms.

This module implement Splash Sync connector for Prestashop. 
It provides access to multiples Objects for automated synchronization though Splash Sync dedicated protocol.

[![Build Status](https://travis-ci.org/SplashSync/Prestashop.svg?branch=master)](https://travis-ci.org/SplashSync/Prestashop)
[![Latest Stable Version](https://poser.pugx.org/splash/prestashop/v/stable)](https://packagist.org/packages/splash/prestashop)
[![Latest Unstable Version](https://poser.pugx.org/splash/prestashop/v/unstable)](https://packagist.org/packages/splash/prestashop)
[![License](https://poser.pugx.org/splash/prestashop/license)](https://packagist.org/packages/splash/prestashop)

## Installation

* Download the latest Mobiloweb version [here](https://github.com/Mobiloweb/Prestashop/releases)
* Copy the module contents into PrestaShop module's folder (modules/splashsync) 
* Enable & Configure the module from the Administration Module's page.
* Set the `PS_PRODUCT_SHORT_DESC_LIMIT` column to **2000** in the `ps_configuration` table

```sql
UPDATE `ps_configuration`
SET value = "2000"
WHERE name = "PS_PRODUCT_SHORT_DESC_LIMIT";
```

## JAMarketplace support

If enabled, you'll need to add the following in **classes/Product.php** (both servers):

`public $ja_shop_name;` at line ~230

`'ja_shop_name' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],` in the fields at line 324

**And add the following on the sender server (not the marketplace)**

`$this->ja_shop_name = Configuration::get('PS_SHOP_NAME');` at the end of the __construct() method (~line 590)

### Change the Splashsync mapping

Move the **prestashop.splash.json** file to the Prestashop **config/** folder and rename it to **splash.json**

## Custom configuration by Mobiloweb

### Recommended configuration for the sender (JMarketplace support enabled)
![image](https://user-images.githubusercontent.com/17336553/118800483-41042180-b8a0-11eb-8c89-f85d56370c89.png)

### Recommended configuration for the receiver (JMarketplace support enabled)
![image](https://user-images.githubusercontent.com/17336553/118800387-2336bc80-b8a0-11eb-9b20-a2c349aa0232.png)


## Requirements

* PHP 7.2+
* PrestaShop 1.6+
* An active Splash Sync User Account

## Documentation

For the configuration guide and reference, see: [Prestashop Module Documentation](https://splashsync.gitlab.io/Prestashop/)

## Contributing

Any Pull requests are welcome! 

This module is part of [SplashSync](http://www.splashsync.com) project.

