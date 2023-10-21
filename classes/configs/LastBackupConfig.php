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
 * Class LastBackupConfig
 * @package PrestaBackupLite\Configs
 */
class LastBackupConfig extends CustomJsonConfig {

    /**
     * @return LastBackupConfig
     */
    public static function getConfig()
    {
        return new static('FA_PB_LITE__LAST_BACKUP_JSON_CONFIG');
    }

    /**
     * @return bool
     */
    public function isFileExist()
    {
       return file_exists($this->file_path);
    }

    /**
     * @return mixed|null
     */
    public function getFilePath()
    {
        if (!empty($this->file_path)) {
            return $this->file_path;
        }

        return false;
    }

    /***
     * @return mixed|null|string
     */
    public function getFileName()
    {
        if (empty($this->file_name)) {
            $this->file_name = basename($this->getFilePath());
        }

        return $this->file_name;
    }

    /**
     * @return float|mixed|null
     */
    public function getFileSize()
    {
        if (empty($this->file_size)) {
            $this->file_size = round(filesize($this->getFilePath()) / 1048576, 3); //  1024 * 1024 = 1048576
        }

        return $this->file_size;
    }

    /**
     * @return false|null|string
     */
    public function getBackupDate()
    {
        if (!empty($this->end_time)) {
            return date('Y-m-d H:i:s', (int) $this->end_time);
        }

        return null;
    }

    /**
     * @return float|null
     */
    public function getExecutedTime()
    {
        if (!empty($this->start_time) && !empty($this->end_time)) {
            return round($this->end_time - $this->start_time,2);
        }

        return null;
    }

    /**
     * @return array
     */
    public function getAttempts()
    {
        if (is_array($this->attempts)) {
            return $this->attempts;
        }

        return [];
    }

    /**
     * @return bool
     */
    public function updateAttempts()
    {
        $this->attempts = array_merge($this->getAttempts(), [date('Y-m-d H:i:s')]);
        return $this->save();
    }

    /**
     * @return int
     */
    public function numberOfAttempts()
    {
        return count($this->getAttempts());
    }

    /**
     * @return bool
     */
    public function canUpload()
    {
        return $this->numberOfAttempts() < 1;
    }
}