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

use PrestaBackupLite\Configs\LastBackupConfig;
use PrestaBackupLite\Configs\TelegramConfig;

/**
 * Class TelegramService
 * @package PrestaBackupLite\Services
 */
class TelegramService {

    const SEND_MESSAGE_METHOD = 'sendMessage';
    const SEND_DOCUMENT_METHOD = 'sendDocument';

    public $telegramConfig;
    public $lastBackupConfig;
    public $filePath;

    /**
     * TelegramService constructor.
     */
    public function __construct()
    {
        $this->telegramConfig = TelegramConfig::getConfig();
        $this->lastBackupConfig = LastBackupConfig::getConfig();

        if ($this->lastBackupConfig->isFileExist()) {
            $this->filePath = $this->lastBackupConfig->getFilePath();
        }
    }

    /**
     * @return TelegramService
     */
    public static function getService()
    {
        return new static();
    }

    /**
     * @return array|bool
     */
    public function handle()
    {
        if (! $this->filePath) {
            $result = $this->sendMessage($this->messageFileError());
            return $this->resultWithMethod($result, static::SEND_MESSAGE_METHOD);
        }

        if ($this->lastBackupConfig->getFileSize() > 50) {
            $result = $this->sendMessage($this->messageMaxSizeError());
            return $this->resultWithMethod($result, static::SEND_MESSAGE_METHOD);
        }

        return $this->resultWithMethod($this->sendDocument(), static::SEND_DOCUMENT_METHOD);
    }

    /**
     * @param $result
     * @param $methodName
     * @return array|bool
     */
    public function resultWithMethod($result, $methodName)
    {
        if ($result !== false) {
            $array = json_decode($result, true);

            if (!empty($array) && is_array($array)) {
                return array_merge(['method' => $methodName], $array);
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function checkConnection()
    {
        return $this->sendMessage($this->messageConnected());
    }

    /*
    |--------------------------------------------------------------------------
    | --- Api Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @param $method
     * @return string
     */
    public function getTelegramApi($method)
    {
        return 'https://api.telegram.org/bot'. $this->telegramConfig->getBotToken(). '/'. $method ;
    }

    /**
     * @return mixed
     */
    public function sendDocument()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getTelegramApi('sendDocument'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $fileInfo = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->filePath);
        $curlFile = new \CURLFile($this->filePath, $fileInfo);

        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'chat_id' => $this->telegramConfig->getChatId(),
            'document' => $curlFile,
            'caption' => $this->messageSendDocument() . $this->partialMessageFooter(),
        ]);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @param $message
     * @return mixed
     */
    public function sendMessage($message)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getTelegramApi('sendMessage'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'chat_id' => $this->telegramConfig->getChatId(),
            'text' => $message . $this->partialMessageFooter(),
            'disable_web_page_preview' => true,
        ]);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | --- Partial Messages
    |--------------------------------------------------------------------------
    */

    /**
     * @return null|string
     */
    public function partialMessagePostNote()
    {
        $postNote = $this->telegramConfig->getPostNote();
        if (!empty($postNote)) {
            return "\n" .'📝 ' . $postNote . "\n";
        }

        return null;
    }

    /**
     * @return string
     */
    public function partialMessageFooter()
    {
        $text = "\n\n";
        $text .= $this->partialMessagePostNote();
        $text .= "\n\n" .'🚀 نسخه پیشرفته پرستابکاپ :' . "\n\n" . '🔗 faraket.com/pb-pro' . "\n\n" . '✅ @Prestafa';
        return $text;
    }

    /**
     * @param bool $fullDetail
     * @return string
     */
    public function partialMessageFileInfo($fullDetail = true)
    {
        $text = '';
        if ($fullDetail) {
            $text .= '📌 نام فایل :'. "\n" . '▫ ' . $this->lastBackupConfig->getFileName();
            $text .= "\n\n" . '📌 حجم فایل :'. "\n" . '▫ ' . $this->lastBackupConfig->getFileSize() . ' MB';
        }

        if (!empty($this->lastBackupConfig->getBackupDate())) {
            $text .= "\n\n" . '📌 تاریخ پشتیبان گیری :'. "\n" . '▫ ' . $this->lastBackupConfig->getBackupDate();
        }

        $text .= "\n\n" . '📌 زمان اجرا :'. "\n" . '▫ ' . $this->lastBackupConfig->getExecutedTime(). ' Second(s)';

        return $text;
    }

    /*
    |--------------------------------------------------------------------------
    | --- Telegram Messages
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function messageConnected()
    {
        return '⚡️ تبریک می‌گوییم! پرستابکاپ لایت فروشگاه خود را به تلگرام متصل کرده‌اید! 😏';
    }

    /**
     * @return string
     */
    public function messageFileError()
    {
        $text = '⚠️ درخواست نامشخص است و یا ممکن است که فایل مورد نظر حذف شده باشد.';

        if (!empty($this->filePath)) {
            $dirName = basename(dirname($this->filePath));
            $text .= "\n\n" . '📌 مسیر فایل :'. "\n\n" . '▫ ' . $dirName .'/'. $this->lastBackupConfig->getFileName();
        }

        return $text;
    }

    /**
     * @return string
     */
    public function messageMaxSizeError()
    {
        $text = '⚠️ متاسفانه فایل پشتیبان گرفته شده دارای حجمی بالاتر از 50 مگابایت است. بات تلگرام اجازه آپلود فایلهای بیشتر از این مقدار را (بدون سرور واسط) نمی‌دهد.';
        $text .= "\n\n\n" . $this->partialMessageFileInfo();
        $text .= "\n\n\n" . '⚡️ پیشنهاد می‌کنیم از نسخه پیشرفته و حرفه‌ای ماژول پرستابکاپ استفاده کنید. در نسخه اصلی پرستابکاپ می‌توانید از سرویس‌های متعدد و مختلف مانند فضای ابری برای ذخیره سازی خودکار فایل‌های پشتیبان خود استفاده کرده و از محدودیت‌های کمتر و امکانات بسیار بیشتر بهره‌مند شوید.';
        return $text;
    }

    /**
     * @return string
     */
    public function messageSendDocument()
    {
        return $this->partialMessageFileInfo(false);
    }
}