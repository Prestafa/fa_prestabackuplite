<?php

/**
 * PrestaBackup Lite - Free PrestaShop Module for Database backup.
 *     With the help of this module, you can easily create a backup
 *     - of the online store database and send the backup file to the cloud.
 *
 * This module developed for PrestaShop Ecommerce Platform.
 *     PrestaShop is International Registered Trademark & Property of PrestaShop SA
 *     For more information about PrestaShop go to https://prestashop.com/
 *
 * Thanks to the following developers and libraries:
 *     - MySQLDump-PHP from Diego Torres <https://github.com/ifsnop/mysqldump-php/>
 *
 *
 * @author      Ali Shareei <alishareei@gmail.com>
 * @website     http://prestafa.com
 * @repository  https://github.com/Prestafa/fa_prestabackuplite/
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html (GNU General Public License v3.0)
 */

/*
|--------------------------------------------------------------------------
| --- Configs
|--------------------------------------------------------------------------
*/

require_once dirname(__FILE__) . '/configs/JsonConfig.php';
require_once dirname(__FILE__) . '/configs/CustomJsonConfig.php';
require_once dirname(__FILE__) . '/configs/TablesConfig.php';
require_once dirname(__FILE__) . '/configs/LastBackupConfig.php';
require_once dirname(__FILE__) . '/configs/CronConfig.php';
require_once dirname(__FILE__) . '/configs/TelegramConfig.php';

/*
|--------------------------------------------------------------------------
| --- Services
|--------------------------------------------------------------------------
*/

require_once dirname(__FILE__) . '/services/DatabaseService.php';
require_once dirname(__FILE__) . '/services/TelegramService.php';

