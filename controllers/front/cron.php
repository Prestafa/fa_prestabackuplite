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

use PrestaBackupLite\Configs\CronConfig;
use PrestaBackupLite\Configs\TelegramConfig;
use PrestaBackupLite\Configs\LastBackupConfig;

use PrestaBackupLite\Services\TelegramService;
use PrestaBackupLite\Services\DatabaseService;

/**
 * Class Fa_prestabackupliteCronModuleFrontController
 */
class Fa_prestabackupliteCronModuleFrontController extends ModuleFrontController
{
    public $cronConfig;
    public $telegramConfig;
    public $lastBackupConfig;

    /*
    |--------------------------------------------------------------------------
    | --- Main Methods
    |--------------------------------------------------------------------------
    */

    /**
     *  Main Process Method
     */
    public function postProcess()
    {
        $this->cronConfig = CronConfig::getConfig();
        $this->cronConfig->setPhpDirectives();
        $this->telegramConfig = TelegramConfig::getConfig();
        $this->lastBackupConfig = LastBackupConfig::getConfig();

        $token = Tools::getValue('token');

        if (! $this->cronConfig->compareTokenWith($token)) {
            return $this->dieWithJsonResponse(-1);
        }

        if ($this->lastBackupConfig->isFileExist() && $this->telegramConfig->isValid()) {
            if ($this->lastBackupConfig->canUpload()) {
                $this->lastBackupConfig->updateAttempts();
                return $this->processTelegram();
            } else {
                $this->lastBackupConfig->saveWithNull();
                return $this->dieWithJsonResponse(-3);
            }
        }

        if ($this->cronConfig->isBackupTime()) {
            return $this->processBackup();
        }

        return $this->dieWithJsonResponse(0);
    }

    /*
    |--------------------------------------------------------------------------
    | --- Process services
    |--------------------------------------------------------------------------
    */

    /**
     * Process for Database backup service
     */
    public function processBackup()
    {
        $dbService = DatabaseService::getService();
        $this->cronConfig->addDelayOnFailure();
        $result = $dbService->generateBackup();

        if ($result === true) {
            $this->cronConfig->updateNextDate();
            $this->dieWithJsonResponse(10);
        }

        $this->dieWithJsonResponse(-10);
    }


    /**
     * Process for Telegram service
     */
    public function processTelegram()
    {
        $result = TelegramService::getService()->handle();

        if ($result === false) {
            $this->dieWithJsonResponse(-20);
        }

        if (
            !empty($result['ok'])
            && !empty($result['method'])
            && $result['method'] === TelegramService::SEND_DOCUMENT_METHOD
        ) {
            $this->lastBackupConfig->saveWithNull();
            $this->dieWithJsonResponse(20);
        }

        $this->dieWithJsonResponse(-25);
    }

    /*
    |--------------------------------------------------------------------------
    | --- Response Method
    |--------------------------------------------------------------------------
    */

    /**
     * @param $code
     */
    public function dieWithJsonResponse($code)
    {
        $list = [
            -25 => $this->module->l('ارسال فایل به تلگرام ناموفق بوده است.'),
            -20 => $this->module->l('متاسفانه درخواست به تلگرام ناموفق است.'),
            -10 => $this->module->l('متاسفانه پشتیبان گیری از دیتابیس موفقیت آمیز نبود.'),
            -3 => $this->module->l('تلاش برای ارسال آخرین پشتیبان ناموفق بوده است.'),
            -2 => $this->module->l('تنظیمات تلگرام نامعتبر است.'),
            -1 => $this->module->l('توکن نامعتبر است.'),
            0 => $this->module->l('هیچ عملیاتی برای انجام پیدا نشد!'),
            10 => $this->module->l('پشتیبان گیری از دیتابیس با موفقیت انجام شد.'),
            20 => $this->module->l('آخرین فایل پشتیبان با موفقیت به تلگرام ارسال شد.'),
        ];

        header('Content-Type: application/json;charset=utf-8');

        $json = json_encode(
            [
                'result_code' => $code,
                'result_message' => !empty($list[$code]) ? $list[$code] : $this->module->l('نتیجه نامشخص است!'),
            ],
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );
        die($json);
    }
}