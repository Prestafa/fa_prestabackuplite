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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/imports.php';

use PrestaBackupLite\Configs\TablesConfig;
use PrestaBackupLite\Configs\CronConfig;
use PrestaBackupLite\Configs\TelegramConfig;
use PrestaBackupLite\Services\DatabaseService;
use PrestaBackupLite\Services\TelegramService;

/**
 * Class Fa_Prestabackuplite
 */
class Fa_Prestabackuplite extends Module
{
    public $errorMessage;

    /**
     * Fa_Prestabackuplite constructor.
     */
    public function __construct()
    {
        $this->name = 'fa_prestabackuplite';
        $this->tab = 'administration';
        $this->author = 'Prestafa';
        $this->version = '1.0.0';

        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('پرستابکاپ لایت (نسخه رایگان)');
        $this->description = $this->l('ماژول رایگان ایجاد پشتیبان از دیتابیس فروشگاه کاری از پرستافا');
    }

    /**
     * @return bool
     */
    public function install()
    {
        $tables = [
            'no_data_tables' => implode(TablesConfig::TABLES_SEPARATOR, $this->getPsStatsTables()),
        ];

        $cron = [
            'cron_token' => substr(md5(time()), 0, 12),
            'cron_cycle' => 72,
        ];

        return parent::install()
            && $this->registerHook('moduleRoutes')
            && Configuration::updateValue('FA_PB_LITE__BACKUPS_BASE_PATH', $this->getDefaultBackupsPath())
            && Configuration::updateValue('FA_PB_LITE__TABLES_JSON_CONFIG', json_encode($tables))
            && Configuration::updateValue('FA_PB_LITE__CRON_JSON_CONFIG', json_encode($cron))
            && Configuration::updateValue('FA_PB_LITE__LAST_BACKUP_JSON_CONFIG', null)
            && Configuration::updateValue('FA_PB_LITE__TELEGRAM_JSON_CONFIG', null);
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName('FA_PB_LITE__BACKUPS_BASE_PATH')
            && Configuration::deleteByName('FA_PB_LITE__TABLES_JSON_CONFIG')
            && Configuration::deleteByName('FA_PB_LITE__CRON_JSON_CONFIG')
            && Configuration::deleteByName('FA_PB_LITE__LAST_BACKUP_JSON_CONFIG')
            && Configuration::deleteByName('FA_PB_LITE__TELEGRAM_JSON_CONFIG');
    }

    /**
     * @return array
     */
    public function hookModuleRoutes()
    {
       return array(
            'module-'.$this->name.'-cron' => array(
                'rule' => 'pb-lite/cron',
                'controller' => 'cron',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                    'controller' => 'cron',
                    'section' => 'index',
                )
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | --- Generate Form Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function getContent()
    {
        $this->context->controller->addCss($this->getPathUri().'/views/css/admin/pb-lite.css');

        return $this->getHeader()
            . $this->renderForm()
            . $this->getFooter();
    }

    /**
     * @return string
     */
    public function renderForm()
    {
        $output = '';
        $tablesConfig = TablesConfig::getConfig();
        $cronConfig = CronConfig::getConfig();
        $telegramConfig = TelegramConfig::getConfig();


        if (Tools::isSubmit('submitGenerateForm')) {
            $cronConfig->setPhpDirectives();
            $dbService = DatabaseService::getService();
            $result = $dbService->generateManualBackup(Tools::getValue('file_name'));

            if ($result === true) {
                $output .= $this->displayConfirmation(
                    $this->l('پشتیبان گیری با موفقیت در مسیر زیر انجام شد!')
                    . $this->displayWithPreTag($dbService->getFilePath())
                );
            } else {
                $output .= $this->displayError($dbService->getErrorMessage());
            }
        }

        if (Tools::isSubmit('submitSettingsForm')) {
            Configuration::updateValue('FA_PB_LITE__BACKUPS_BASE_PATH', Tools::getValue('FA_PB_LITE__BACKUPS_BASE_PATH'));

            $tablesConfig->reset([
                'selection_type' => Tools::getValue('selection_type'),
                'selection_tables' => Tools::getValue('selection_tables'),
                'no_data_tables' => Tools::getValue('no_data_tables'),
            ])->save();

            Configuration::updateValue(
                'PS_BACKUP_DROP_TABLE',
                !empty(Tools::getValue('PS_BACKUP_DROP_TABLE')) ? '1' : null
            );

            $output .= $this->displayConfirmation($this->l('تنظیمات با موفقیت به روز رسانی شد!'));
        }

        if (Tools::isSubmit('submitCronJobForm')) {
            $cronConfig->reset([
                'cron_token' => Tools::getValue('cron_token'),
                'cron_cycle' => Tools::getValue('cron_cycle'),
                'cron_next_date' => Tools::getValue('cron_next_date'),
                'delay_on_failure' => Tools::getValue('delay_on_failure'),
                'max_execution_time' => Tools::getValue('max_execution_time'),
            ])->save();

            $output .= $this->displayConfirmation($this->l('تنظیمات کرونجاب با موفقیت به روز رسانی شد!'));
        }

        if (Tools::isSubmit('submitTelegramForm')) {
            $telegramConfig->reset([
                'bot_token' => Tools::getValue('bot_token'),
                'chat_id' => Tools::getValue('chat_id'),
                'post_note' => Tools::getValue('post_note'),
            ])->save();

            TelegramService::getService($telegramConfig)->checkConnection();

            $output .= $this->displayConfirmation($this->l('تنظیمات تلگرام با موفقیت به روز رسانی شد! آیا پیام اتصال موفقیت آمیز را در تلگرام دریافت کردید؟'));
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => [
                'file_name' => Tools::getValue('file_name'),

                'FA_PB_LITE__BACKUPS_BASE_PATH' => $this->getFormFieldValue('FA_PB_LITE__BACKUPS_BASE_PATH'),
                'PS_BACKUP_DROP_TABLE' => $this->getFormFieldValue('PS_BACKUP_DROP_TABLE'),

                'selection_type' => Tools::getValue('selection_type', $tablesConfig->getSelectionType()),
                'selection_tables' => Tools::getValue('selection_tables', $tablesConfig->getSelectionTablesString()),
                'no_data_tables' => Tools::getValue('no_data_tables', $tablesConfig->getNoDataTablesString()),

                'cron_token' => Tools::getValue('cron_token', $cronConfig->getToken()),
                'cron_cycle' => Tools::getValue('cron_cycle', $cronConfig->getCycle()),
                'cron_next_date' => Tools::getValue('cron_next_date', $cronConfig->getNextDate()),
                'delay_on_failure' => Tools::getValue('delay_on_failure', $cronConfig->getDelayOnFailure()),
                'max_execution_time' => Tools::getValue('max_execution_time', $cronConfig->getMaxExecutionTime()),

                'bot_token' => Tools::getValue('bot_token', $telegramConfig->getBotToken()),
                'chat_id' => Tools::getValue('chat_id', $telegramConfig->getChatId()),
                'post_note' => Tools::getValue('post_note', $telegramConfig->getPostNote()),
            ],
        ];

        return $output . $helper->generateForm([
            $this->getGenerateForm(),
            $this->getSettingsForm(),
            $this->getCronJobForm(),
            $this->getTelegramForm(),
        ]);
    }

    /**
     * @param $config
     * @return mixed
     */
    public function getFormFieldValue($config)
    {
        return Tools::getValue($config, Configuration::get($config));
    }

    /*
    |--------------------------------------------------------------------------
    | --- Form Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return array
     */
    public function getGenerateForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('پشتیبان گیری دستی (بر اساس تنظیمات پشتیبان گیری)'),
                    'icon' => 'icon-rocket',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('نام فایل'),
                        'name' => 'file_name',
                        'desc' => $this->l('نام فایل اجباری نیست و میتوانید آن را خالی رها کنید. در صورت وارد کردن باید به صورت انگلیسی و بدون فاصله بوده و همچنین نباید شامل کاراکترهای غیر مجاز باشد. همچنین نیازی به وارد کردن پسوند فایل نیست. ضمنا در نام فایل میتوانید متغیر تاریخ {date} را استفاده کنید. این نام در هیچ کجا ذخیره نمیشود.'),
                        'class' => 'input text-right pb-lite-input-ltr',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('راهنما و سلب مسئولیت ها را خواندم، یک پشتیبان جدید ایجاد کن!'),
                    'name' => 'submitGenerateForm',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getSettingsForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('تنظیمات پشتیبان گیری'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('مسیر پشتیبان گیری'),
                        'name' => 'FA_PB_LITE__BACKUPS_BASE_PATH',
                        'desc' => $this->l(' نام فایل باید به صورت انگلیسی و بدون فاصله بوده و همچنین نباید شامل دارای کاراکترهای غیر مجاز باشد. نیازی به وارد کردن پسوند فایل نیست. متغیرهای قابل استفاده برای مسیر پویا بر اساس سال-ماه-روز عبارت اند از {Y}, {Y-m}, {Y-m-d}'),
                        'class' => 'input text-right pb-lite-input-ltr',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('نوع گزینش جداول منتخب'),
                        'name' 	=> 'selection_type',
                        'options' => [
                            'query' => [
                                ['id' => null, 'name' => $this->l('هیچکدام (همه جداول)')],
                                ['id' => TablesConfig::BLACK_LIST_TABLES, 'name' => $this->l('فهرست سیاه')],
                                ['id' => TablesConfig::WHITE_LIST_TABLES, 'name' => $this->l('فهرست سفید')],
                            ],
                            'id' => 'id',
                            'name' => 'name'
                        ],
                        'desc' => $this->l('اگر فهرست سیاه انتخاب شود از پشتیبان گیری از جداول منتخب صرف نظر میشود اما اگر فهرست سفید انتخاب شود تنها از اطلاعات جداول منتخب پشتیبان گرفته میشود.'),
                        'class' => 'input text-right pb-lite-input-ltr',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('فهرست جداول منتخب'),
                        'name' => 'selection_tables',
                        'desc' => $this->l('اگر فهرست سفید یا سیاه انتخاب شود این گزینه مفید خواهد بود. بدین ترتیب جداول منتخب را در این فیلد وارد کنید و هر کدام را با کاراکتر کاما « , » جدا کنید. این جداول منتخب بر اساس گزینه انتخاب شده در بالا به عنوان فهرست سفید یا سیاه در نظر گرفته میشوند.'),
                        'class' => 'input text-right pb-lite-input-ltr',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('در نظر نگرفتن اطلاعات جداول'),
                        'name' => 'no_data_tables',
                        'desc' => $this->l(' اطلاعات جداولی که در این قسمت با کاما « , » از یکدیگر جدا میشوند در هنگام پشتیبان گیری در نظر گرفته نمیشود. جداولی که اطلاعات آنها ضروری نیستند مانند جداول لاگ و گزارشات و آمارها را میتوان در این قسمت وارد کرد. به عنوان مثال فهرست جداول آمار پرستاشاپ به شرح زیر است:')
                            . '<br>'. implode(TablesConfig::TABLES_SEPARATOR, $this->getPsStatsTables()),
                        'class' => 'input text-right pb-lite-input-ltr',
                    ],
                    [
                        'type' 	=> 'switch',
                        'label' => $this->l('رهاکردن جداول موجود در هنگام پشتیبان گیری'),
                        'name' 	=> 'PS_BACKUP_DROP_TABLE',
                        'desc' => $this->l('اگر فعال شده باشد، در هنگام پشتیبان گیری دستوری قبل از هر جدول ثبت میشود که اگر در هنگام بازگردانی ان جدول موجود باشد، آن جدول را به صورت کامل حذف میکند. توجه کنید که این گزینه جداول قبلی را حذف کرده و دوباره جداول را با اطلاعات موجود در فایل پشتیبان ایجاد میکند. (به این معنا که "DROP TABLE IF EXISTS") (این گزینه از هسته پرستاشاپ یعنی بخش «پارامترهای پیشرفته > پایگاه داده > پشتیبان پایگاه داده» است)'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('بله'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('خیر'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('ذخیره تنظیمات پشتیبان گیری'),
                    'name' => 'submitSettingsForm',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getCronJobForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('تنظیمات کرونجاب'),
                    'icon' => 'icon-time',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('توکن امنیتی کرونجاب'),
                        'name' => 'cron_token',
                        'desc' => $this->l('توکن امنیتی کرونجاب مانند رمز عبور است. در صورتی که این توکن را تغییر دهید دستور کرونجاب را نیز باید به روز رسانی کنید.'),
                        'class' => 'input fixed-width-xxl text-right pb-lite-input-ltr',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('چرخه زمانی کرونجاب'),
                        'name' => 'cron_cycle',
                        'class' => 'input fixed-width-sm text-right',
                        'prefix' => $this->l('هر'),
                        'suffix' => $this->l('ساعت'),
                        'desc' => $this->l('پشتیبان گیری خودکار توسط کرونجاب بر اساس این بازه زمانی مشخص شده انجام میشود. این مقدار باید به صورت عددی و بر اساس چرخه ساعتی مورد نیاز باشد. میتوانید این مقدار را به صورت اعشاری وارد کنید مثلا 0.5 یعنی هر نیم ساعت یا 0.1 یعنی هر 6 دقیقه.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('تاخیر در صورت شکست'),
                        'name' => 'delay_on_failure',
                        'class' => 'input fixed-width-sm text-right',
                        'suffix' => $this->l('دقیقه'),
                        'desc' => $this->l('در صورتی که پشتیبان گیری از دیتابیس با شکست مواجه شد ، تلاش بعدی به این میزان به تاخیر خواهد افتاد. این میزان برای جلوگیری از تکرار شکست در بازه زمانی کوتاه و کاهش فشار به سرور مناسب است. مقدار پیشفرض 60 دقیقه است.'),
                    ],
                    [
                        'type' => 'datetime',
                        'label' => $this->l('زمان پشتیبان گیری خودکار بعدی'),
                        'name' => 'cron_next_date',
                        'desc' => $this->l('این تاریخ بر اساس آخرین عملیات پشتیبان گیری و بر اساس چرخه زمانی تعیین شده به صورت خودکار به روز رسانی میشود. نیازی به تغییر این مقدار نیست مگر اینکه بخواهید زمان پشتیبان گیری بعدی را تغییر دهید.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('محدودیت زمان اجرای اسکریپت'),
                        'name' => 'max_execution_time',
                        'class' => 'input fixed-width-sm text-right',
                        'suffix' => $this->l('ثانیه'),
                        'desc' => sprintf($this->l('در صورتی که دیتابیس فروشگاه سنگین بوده و همچنین هاست شما دسترسی تغییر تنظیمات PHP برای متغیر max_execution_time را میدهد میتوانید مقدار مناسب را وارد کنید. با این حال توصیه اصلی بر این است که تا میتوانید با کمک تنظیمات پشتیبان سبکی ایجاد کنید. مقدار پیشفرض %s است.'), ini_get('max_execution_time')),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('ذخیره تنظیمات کرونجاب'),
                    'name' => 'submitCronJobForm',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getTelegramForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('تنظیمات سرویس تلگرام'),
                    'icon' => 'icon-cloud-upload',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('توکن بات تلگرام'),
                        'name' => 'bot_token',
                        'desc' => $this->l('اگر میخواهید فایل پشتیبان شما به تلگرام شما ارسال شود. باید یک بات تلگرام ساخته و توکن این بات را در این قسمت وارد کنید.'),
                        'class' => 'input text-right pb-lite-input-ltr',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('شناسه چت گیرنده فایل'),
                        'name' => 'chat_id',
                        'class' => 'input fixed-width-lg text-right pb-lite-input-ltr',
                        'desc' => $this->l('برای ارسال فایل به شما از طریق بات تلگرام نیاز به شناسه چت شما است. این شناسه را میتوانید از طریق باتهایی مانند https://t.me/RawDataBot دریافت کنید. یک پیام به این بات ارسال کنید و از قسمت from -> id یا chat -> id شناسه چت خود را در این قسمت وارد کنید.'),
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('یادداشت کوتاه تلگرام'),
                        'name' => 'post_note',
                        'desc' => $this->l('اگر میخواهید در تمام پستهای بات تلگرام یادداشتی نمایش داده شود میتوانید یادداشت کوتاه خود را در این قسمت وارد کنید. توصیه میکنیم از وارد کردن محتوای طولانی خودداری کنید. مثلا هشتگ اختصاصی نام فروشگاه ، یا توضیحاتی کوتاه درباره محتوای پشتیبان گرفته شده و ....'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('ذخیره و ارسال پیام اتصال به تلگرام'),
                    'name' => 'submitTelegramForm',
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | --- Header & Footer Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->context->smarty->fetch(dirname(__FILE__) .'/views/templates/admin/header.tpl');
    }

    /**
     * @return string
     */
    public function getFooter()
    {
        $this->context->smarty->assign([
            'cron_link' => $this->getCronJobLink(),
        ]);

        return $this->context->smarty->fetch(dirname(__FILE__) .'/views/templates/admin/footer.tpl');
    }

    /*
    |--------------------------------------------------------------------------
    | --- Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return array
     */
    public function getPsStatsTables()
    {
        return [
            _DB_PREFIX_ . 'connections',
            _DB_PREFIX_ . 'connections_page',
            _DB_PREFIX_ . 'connections_source',
            _DB_PREFIX_ . 'guest',
            _DB_PREFIX_ . 'statssearch',
        ];
    }

    /**
     * @return mixed
     */
    public function getDefaultBackupsPath()
    {
        $path = dirname(_PS_ROOT_DIR_, 1). '\\'.$this->name .'\\backup-{Y-m}\\{Y-m-d}\\';
        return str_replace('\\', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @return mixed
     */
    public function getCronJobLink()
    {
        $params = [
            'token' => CronConfig::getConfig()->getToken()
        ];

        return $this->context->link->getModuleLink($this->name, 'cron', $params, true);
    }

    /**
     * @param $content
     * @return string
     */
    public function displayWithPreTag($content)
    {
        return '<pre style="margin: 15px 0 0 0;direction: ltr;">' . $content . '</pre>';
    }
}