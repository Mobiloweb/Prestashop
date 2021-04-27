[![N|Solid](https://github.com/SplashSync/Php-Core/raw/master/img/github.jpg)](https://www.splashsync.com)
![Logo-Mobiloweb-Ok-2017-1-1-seul](https://user-images.githubusercontent.com/17336553/116259806-6c4e9180-a776-11eb-8157-b74f50baf166.png)

# Splash Sync Module for PrestaShop - MOBILOWEB VERSION
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
Now, you can refresh the servers and you should see a new "Shop name" field appearing in the mapping. 

Just add it to the configuration:
![image](https://user-images.githubusercontent.com/17336553/116259479-1ed22480-a776-11eb-80ef-a12436995c41.png)

**And don't forget to activate it.**

## Custom configuration by Mobiloweb

### Configuration available
![Configuration](https://i.imgur.com/DIfWfVs.png)

### Recommended configuration for the sender (JMarketplace support enabled)
![image](https://user-images.githubusercontent.com/17336553/116258156-fc8bd700-a774-11eb-82eb-ff55d025fdec.png)

### Recommended configuration for the receiver (JMarketplace support enabled)
![image](https://user-images.githubusercontent.com/17336553/116259088-ca2ea980-a775-11eb-9578-aa3e2de45399.png)


## Requirements

* PHP 7.2+
* PrestaShop 1.6+
* An active Splash Sync User Account

## Documentation

For the configuration guide and reference, see: [Prestashop Module Documentation](https://splashsync.gitlab.io/Prestashop/)

## Contributing

Any Pull requests are welcome! 

This module is part of [SplashSync](http://www.splashsync.com) project.

