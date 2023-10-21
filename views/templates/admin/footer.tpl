<div id="pb-lite-footer" class="panel">
  <div class="panel-heading">
    <i class="icon-info-sign"></i>
      {l s='راهنمای ماژول و سلب مسئولیت ها' mod='fa_prestabackuplite'}
  </div>
  <div class="panel-body" style="font-size: 16px;">
      <ul>
          <li>{l s='لطفا قبل از هر کاری توضیحات مربوط به «سلب مسئولیت» و «چگونگی بازگردانی» را از بخش «پارامترهای پیشرفته > پایگاه داده > پشتیبان پایگاه داده» مطالعه فرمایید.' mod='fa_prestabackuplite'}</li>
          <li>{l s='لازم به تاکید است که این ماژول و توسعه دهنده آن (پرستافا/فراکت) در هیچ حالتی مسئولیتی در قبال فروشگاه و داده های شما ندارند.' mod='fa_prestabackuplite'}</li>
          <li>{l s='این ماژول به صورت منبع باز و رایگان و تحت مجوز GPL ارائه شده است و انتظار داریم به قوانین این مجوز احترام گذاشته شود.' mod='fa_prestabackuplite'}</li>
          <li>{l s=' خواهشمندیم به منظور حمایت از توسعه دهنده ماژول در هر حالتی لینکهای ارجاع به پرستافا/فراکت و نسخه پیشرفته پرستابکاپ حفظ شود.' mod='fa_prestabackuplite'}</li>
          <li>
              {l s='این ماژول نسخه رایگان و منبع باز ماژول پشتیبان گیری پیشرفته و خودکار پرستابکاپ بوده و تنها کمتر از یک دهم امکانات نسخه اصلی را داراست.' mod='fa_prestabackuplite'}
              <a target="_blank" href="https://faraket.com/pb-pro">
                  {l s='توضیحات بیشتر' mod='fa_prestabackuplite'}
              </a>
              <div class="alert alert-success" style="margin-top: 20px;">
                  <h4>{l s='گوشه ای از امکانات نسخه پیشرفته پرستابکاپ' mod='fa_prestabackuplite'}</h4>
                  <ul>
                      <li>{l s='امکان ایجاد محدودیت زمانی فعالیت کرونجاب در طول شبانه روز' mod='fa_prestabackuplite'}</li>
                      <li>{l s='امکان بخش بندی و تکه کردن پشتیبانها و ایجاد بی نهایت فرآیند و برنامه پشتیبان گیری' mod='fa_prestabackuplite'}</li>
                      <li>{l s='امکان آپلود پشتیبان برروی سرورها و سرویس ها مختلف' mod='fa_prestabackuplite'}</li>
                      <li>{l s='امکان افزودن وبسرویسهای مختلف و تعریف برای هر بخش به دلخواه' mod='fa_prestabackuplite'}</li>
                      <li>{l s='امکان افزودن بی نهایت اکانت هر وبسرویس با توجه به نیاز' mod='fa_prestabackuplite'}</li>
                      <li>{l s='امکان ارسال کپی از پشتیبان به چند سرور و فضای ابری مختلف' mod='fa_prestabackuplite'}</li>
                      <li>{l s='امکان بررسی دقیق گزارشات و لاگها برای اطمینان از عملکرد مناسب و مفید' mod='fa_prestabackuplite'}</li>
                      <li>{l s='امکان استفاده از الگوی آدرس دهی هوشمند و بسیار منعطف' mod='fa_prestabackuplite'}</li>
                      <li>{l s='امکان تنظیم پشتیبان گیری بر اساس بازه زمانی دقیقه ، ساعت ، روز ، هفته و ماه' mod='fa_prestabackuplite'}</li>
                      <li>{l s='امکان صف بندی پشتیبان گیری و پشتیبان گیری زنجیره ای و پیوسته' mod='fa_prestabackuplite'}</li>
                      <li>
                          {l s='و دهها امکان و تنظیم بیشتر که به مرور به نسخه پیشرفته اضافه میشوند!' mod='fa_prestabackuplite'}
                          <a target="_blank" href="https://faraket.com/pb-pro">
                              {l s='بیشتر بدانید ...' mod='fa_prestabackuplite'}
                          </a>
                      </li>
                  </ul>
              </div>

          </li>
          <li>
              {l s='در صورتی که میخواهید از سرویس پشتیبان گیری خودکار و یا سرویس فضای ابری تلگرام استفاده کنید باید حتما بر روی هاست خود کرونجاب مناسب را تنظیم کنید.' mod='fa_prestabackuplite'}
              {l s='لطفا یکی از دستورات زیر را متناسب با کنترل پنل هاست خود انتخاب و تنظیم کنید.' mod='fa_prestabackuplite'}
              <pre class="pb-lite-pre">* * * * * /usr/local/bin/curl -L "{$cron_link}"</pre>
              {l s='و یا دستور زیر:' mod='fa_prestabackuplite'}
              <pre class="pb-lite-pre">* * * * * curl -L "{$cron_link}"</pre>

          </li>
          <li>{l s='توجه داشته باشید که در هاست لوکیشن ایران ممکن است دسترسی به تلگرام بسته یا فیلتر شده باشد.' mod='fa_prestabackuplite'}</li>
          <li>{l s='حداکثر حجم قابل ارسال به بات تلگرام (بدون سرور واسط) 50 مگابایت است. (این یعنی یک دیتابیس تا حدود 700 مگابایت بدون فشرده سازی)' mod='fa_prestabackuplite'}</li>
          <li>{l s='در صورتی که دیتابیس سنگینی دارید توصیه میکنیم فقط جداول مهم را پشتیبان خودکار بگیرید و یا اینکه از نسخه پیشرفته پرستابکاپ استفاده کنید.' mod='fa_prestabackuplite'}</li>
          <li>{l s='مسئولیت استفاده از تلگرام و بات تلگرام به عنوان فضای ابری و ذخیره سازی بر عهده شما می باشد.' mod='fa_prestabackuplite'}</li>
          <li>{l s='به منظور رفع محدودیتهای موجود پیشنهاد میکنیم از نسخه پیشرفته ماژول با امکانات بسیار بیشتر و منعطف تر استفاده کنید.' mod='fa_prestabackuplite'}</li>
          <li>{l s='در نهایت آرزومندیم این ماژول برای شما و کسب و کار شما مفید و ارزشمند واقع شود.' mod='fa_prestabackuplite'}</li>
      </ul>

      <br />

      <a class="btn btn-default" target="_blank" href="https://faraket.com/">
          <i class="icon-ok-sign"></i>
          {l s='ورود به وبسایت پرستافا/فراکت' mod='fa_prestabackuplite'}
      </a>

      <div class="pull-right">
          <a class="btn btn-default" target="_blank" href="https://faraket.com/pb-lite">
              <i class="icon-puzzle-piece"></i>
              {l s='مشاهده و دریافت آخرین نسخه پرستابکاپ لایت' mod='fa_prestabackuplite'}
          </a>
          <a class="btn btn-success" target="_blank" href="https://faraket.com/pb-pro">
              <i class="icon-rocket"></i>
              {l s='دریافت نسخه پیشرفته پرستابکاپ' mod='fa_prestabackuplite'}
          </a>
      </div>
  </div>
    <div class="panel-footer">
        <div class="pull-left">
            <i class="icon-code"></i>
            {l s='با افتخار توسعه داده شده توسط پرستافا/فراکت.' mod='fa_prestabackuplite'}
            <a target="_blank" href="https://faraket.com/">
                {l s='دریافت ماژولهای بیشتر ...' mod='fa_prestabackuplite'}
            </a>
        </div>
        <div class="pull-right">
            <i class="icon-pushpin"></i>
            {l s='لینک های مفید :' mod='fa_prestabackuplite'}
            <a target="_blank" href="https://github.com/Prestafa/fa_prestabackuplite">
                {l s='مخزن گیتهاب پرستابکاپ لایت' mod='fa_prestabackuplite'}
            </a>
            {l s='و' mod='fa_prestabackuplite'}
            <a target="_blank" href="https://github.com/ifsnop/mysqldump-php">
                {l s='کتابخانه MySQLDump' mod='fa_prestabackuplite'}
            </a>
        </div>
    </div>
</div>


