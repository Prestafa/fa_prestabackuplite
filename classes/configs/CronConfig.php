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
 * Class CronConfig
 * @package PrestaBackupLite\Configs
 */
class CronConfig extends CustomJsonConfig {

    /**
     * @return CronConfig
     */
    public static function getConfig()
    {
        return new static('FA_PB_LITE__CRON_JSON_CONFIG');
    }

    /**
     * @return mixed|null
     */
    public  function getToken()
    {
        return $this->cron_token;
    }

    /**
     * @return float
     */
    public function getCycle()
    {
        return (float) $this->cron_cycle;
    }

    /**
     * @return mixed|null
     */
    public  function getMaxExecutionTime()
    {
        return $this->max_execution_time;
    }

    /**
     * @return mixed|null
     */
    public function getNextDate()
    {
        return $this->cron_next_date;
    }

    /**
     * @param $string
     * @return bool
     */
    public function compareTokenWith($string)
    {
        if (empty($this->getToken()) || $this->getToken() != $string) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isBackupTime()
    {
        return empty($this->getNextDate()) || time() > strtotime($this->getNextDate());
    }

    /**
     * @return mixed|null
     */
    public  function getDelayOnFailure()
    {
        return $this->delay_on_failure;
    }

    /**
     * @return bool
     */
    public function addDelayOnFailure()
    {
        $delay = (int) $this->getDelayOnFailure();
        $minutes = !empty($delay) && $delay > 0 ? $delay : 60;
        $this->cron_next_date = date('Y-m-d H:i:s', strtotime("+$minutes minutes"));
        return $this->save();
    }

    /**
     * @return bool
     */
    public function updateNextDate()
    {
        $minutes = $this->getCycle() * 60;
        $minutes = ($minutes > 1) ? intval($minutes) : 1;
        $this->cron_next_date = date('Y-m-d H:i:s', strtotime("+$minutes minutes"));
        return $this->save();
    }

    /**
     * @return bool|null|string
     */
    public function setPhpDirectives()
    {
        $maxExecTime = $this->getMaxExecutionTime();
        if (!empty($maxExecTime) && is_numeric($maxExecTime)) {
            return @ini_set('max_execution_time', $maxExecTime);
        }

        return null;
    }
}
