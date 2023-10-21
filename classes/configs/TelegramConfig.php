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

namespace PrestaBackupLite\Configs;


/**
 * Class TelegramConfig
 * @package PrestaBackupLite\Configs
 */
class TelegramConfig extends CustomJsonConfig {

    /**
     * @return TelegramConfig
     */
    public static function getConfig()
    {
        return new static('FA_PB_LITE__TELEGRAM_JSON_CONFIG');
    }

    /**
     * @return mixed|null
     */
    public  function getBotToken()
    {
        return $this->bot_token;
    }

    /**
     * @return mixed|null
     */
    public function getChatId()
    {
        return $this->chat_id;
    }

    /**
     * @return mixed|null
     */
    public function getPostNote()
    {
        return $this->post_note;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return !empty($this->getBotToken()) && !empty($this->getChatId());
    }
}