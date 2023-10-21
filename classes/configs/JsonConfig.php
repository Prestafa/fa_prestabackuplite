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

use Configuration;

/**
 * Class JsonConfig
 * @package PrestaBackupLite\Configs
 */
class JsonConfig {

    public $configName = null;
    public $configArray = [];
    public $undefinedValue = null;

    /*
    |--------------------------------------------------------------------------
    | --- Main Methods
    |--------------------------------------------------------------------------
    */

    /**
     * JsonConfig constructor.
     * @param $configName
     */
    public function __construct($configName)
    {
        $configJson = Configuration::get($configName);
        $configArray = json_decode($configJson, true);

        $this->configName = $configName;
        $this->configArray = is_array($configArray) ? $configArray : [];
    }

    /**
     * @param $configName
     * @return JsonConfig
     */
    public static function load($configName)
    {
        return new static($configName);
    }

    /*
    |--------------------------------------------------------------------------
    | --- Magic Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        if (isset($this->configArray[$key])) {
            return $this->configArray[$key];
        }

        return $this->undefinedValue;
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->configArray[$key] = $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->configArray[$key]);
    }

    /**
     * @param $key
     */
    public function __unset($key)
    {
        unset($this->configArray[$key]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->getArray());
    }

    /*
    |--------------------------------------------------------------------------
    | --- Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @param $value
     */
    public function setUndefinedValue($value)
    {
        $this->undefinedValue = $value;
    }

    /**
     * @return array|mixed
     */
    public function getArray()
    {
        return $this->configArray;
    }

    /**
     * @return string
     */
    public function getJson()
    {
        return (string) $this;
    }

    /*
    |--------------------------------------------------------------------------
    | --- Data Management Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @param array $additionalData
     * @return $this
     */
    public function merge(array $additionalData = [])
    {
        $this->configArray = array_merge($this->configArray, $additionalData);
        return $this;
    }

    /**
     * @param array $newData
     * @return $this
     */
    public function reset(array $newData = [])
    {
        $this->configArray = $newData;
        return $this;
    }

    /**
     * @return JsonConfig
     */
    public function reload()
    {
        $loadConfig = new static($this->configName);
        return $this->reset($loadConfig->getArray());
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function update($key, $value)
    {
        $this->configArray[$key] = $value;
        return $this->save();
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (empty($this->configName)) {
            return false;
        }

        return Configuration::updateValue($this->configName, $this->getJson());
    }

    /**
     * @return bool
     */
    public function saveWithNull()
    {
        if (empty($this->configName)) {
            return false;
        }

        return Configuration::updateValue($this->configName, null);
    }
}