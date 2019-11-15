# Mage2 Module OrviSoft FeedCronScripts

    `orvisoft/module-feedcronscripts`

 - [Main Functionalities](#main-functionalities)
 - [Installation](#installation)
 - [Configuration](#configuration)
 - [Specifications](#specifications)

## Main Functionalities
This module helps to create feeds for Google, Pixel, PriceSearcher and WebCollage.

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/OrviSoft/FeedCronScripts`
 - Enable the module by running `php bin/magento module:enable OrviSoft_FeedCronScripts`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require orvisoft/module-feedcronscripts`
 - enable the module by running `php bin/magento module:enable OrviSoft_FeedCronScripts`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

## Configuration

 - Google Feed Status (feeds/googlefeed/enabled)

 - Google Feed File Name (feeds/googlefeed/file_name)

 - Pixel Feed Status (feeds/pixelfeed/enabled)

 - Pixel Feed File Name (feeds/pixelfeed/file_name)

 - Price Searcher Status (feeds/pricesearcher/enabled)

 - Price Searcher File Name (feeds/pricesearcher/file_name)

 - WebCollage Status (feeds/webcollage/enabled)

 - WebCollage File Name (feeds/webcollage/file_name)

## Specifications

 - Cronjob(s)
	- orvisoft_feedcronscripts_googlefeed
	
	- orvisoft_feedcronscripts_pixedfeed

	- orvisoft_feedcronscripts_pricesearcher

	- orvisoft_feedcronscripts_webcollage

 - Helper
	- OrviSoft\FeedCronScripts\Helper\Data

