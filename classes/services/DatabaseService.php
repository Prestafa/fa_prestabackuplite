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

namespace PrestaBackupLite\Services;

require_once dirname(__FILE__, 3) . '/third-party/ifsnop-mysqldump/Mysqldump.php';

use Configuration;
use FaThirdParty\Ifsnop\Mysqldump as MysqlDumper;
use PrestaBackupLite\Configs\TablesConfig;
use PrestaBackupLite\Configs\LastBackupConfig;

/**
 * Class DatabaseService
 * @package PrestaBackupLite\Services
 */
class DatabaseService {

    public $basePath;
    public $fileName;
    public $tablesConfig;
    public $errorMessage;

    /*
    |--------------------------------------------------------------------------
    | --- Main Methods
    |--------------------------------------------------------------------------
    */

    /**
     * DatabaseService constructor.
     */
    public function __construct()
    {
        $basePath = $this->getRawBasePath();
        $basePath = $this->parsePath($basePath);

        if (! is_dir($basePath)) {
            @mkdir($basePath, 0755, true);
        }

        $this->basePath = $basePath;
        $this->tablesConfig = TablesConfig::getConfig();
        $this->fileName = $this->generateFileName();
    }

    /**
     * @return DatabaseService
     */
    public static function getService()
    {
        return new static();
    }

    /*
    |--------------------------------------------------------------------------
    | --- Generate Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @param null $name
     * @return bool
     */
    public function generateManualBackup($name = null)
    {
        $fileName = str_replace(' ', '', $name);
        $fileName = strlen($fileName) > 0 ? $fileName : 'manual-backup-{date}';
        $fileName = str_replace('{date}', date('Y_m_d-H_i'), $fileName);
        $this->fileName = time() . '-'. $fileName . $this->getFileFormat();

        return $this->dump();
    }

    /**
     * @return bool
     */
    public function generateBackup()
    {
        return $this->dump();
    }

    /*
    |--------------------------------------------------------------------------
    | --- Dump Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return bool
     */
    public function dump()
    {
        $startTime = microtime(true);

        try {
            $dump = new MysqlDumper\Mysqldump(
                'mysql:host='._DB_SERVER_.';dbname='._DB_NAME_,
                _DB_USER_,
                _DB_PASSWD_,
                [
                    'include-tables' => $this->tablesConfig->getWhiteListTablesArray(),
                    'exclude-tables' => $this->tablesConfig->getBlackListTablesArray(),
                    'no-data'  => $this->tablesConfig->getNoDataTablesArray(),
                    'compress' => $this->getCompressType(),
                    'add-drop-table' => (bool) Configuration::get('PS_BACKUP_DROP_TABLE'),
                ]
            );

            $dump->start($this->getFilePath());

            LastBackupConfig::getConfig()->reset([
                'file_path' => $this->getFilePath(),
                'start_time' => !empty($GLOBALS['start_time']) ? $GLOBALS['start_time'] : $startTime,
                'end_time' => microtime(true),
            ])->save();

            return true;
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }

        return false;
    }

    /**
     * @return string
     */
    public function getCompressType()
    {
        if (function_exists("gzopen")) {
            return MysqlDumper\Mysqldump::GZIP;
        }

        return MysqlDumper\Mysqldump::NONE;
    }

    /*
    |--------------------------------------------------------------------------
    | --- Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function getRawBasePath()
    {
        $path = Configuration::get('FA_PB_LITE__BACKUPS_BASE_PATH');
        return rtrim($path, '\/') . DIRECTORY_SEPARATOR;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function parsePath($path)
    {
        $vars = [
            '{Y}'       => date('Y'),
            '{Y-m}'     => date('Y-m'),
            '{Y-m-d}'   => date('Y-m-d'),
        ];

        foreach ($vars as $var => $value) {
            $path = str_replace($var, $value, $path);
        }

        return $path;
    }

    /**
     * @return string
     */
    public function generateFileName()
    {
        return time() . '-auto-backup-'. date('Y_m_d-H_i') . $this->getFileFormat();
    }

    /**
     * @return string
     */
    public function getFileFormat()
    {
        if (function_exists("gzopen")) {
            return '.sql.gz';
        }

        return '.sql';
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->basePath . $this->fileName;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}