# maib Payment Gateway for WHMCS

#EN

**INTRODUCTION**

The module integrates WHMCS with **maib** ecommerce.

**REQUIREMENTS**

CURL

**BEFORE USAGE**

Send email to **maib** ecommerce support (ecom@maib.md) with your website external ***IP*** and ***Callback URL***.

**INSTALLATION**

1. Upload folder ```modules``` to WHMCS root directory
2. WHMCS 7: Activate module through the menu: 'Setup->Payments->Payment Gateways'

   WHMCS 8: Activate module through the menu: 'Addons->Apps & Integrations->Browse->Payments->maib'
   
**CONFIGURATION**

***Display Name*** - The name of the payment gateway that customers see on the website.

***Test Mode*** - Tick to enable test mode. The following tests are required: payment, refund, closing of business day.

***Callback*** - Send this URL and website IP to **maib** ecommerce support.

***Certificate*** - Path to certificate for request authorization. By default path to test certificate. 

***Key certificate*** - Path to key certificate for request authorization. By default path to test key certificate. 

***Password*** - Certificate password. Test certificate password: Za86DuC

***Closing of business day*** -  Closing of business day URL. Run this URL with CRON job every day at 23:59!

**LIVE MODE**

For live mode **maib** will provide certificate in *.pfx* format and certificate password.

Use openssl to convert certificate in *.pem* format from *.pfx* and password provided:

        # Public certificate:
          openssl pkcs12 -in certname.pfx -nokeys -out cert.pem
        # The private key with password:
          openssl pkcs12 -in certname.pfx -nocerts -out key.pem
        
Disable test mode, add path to live certificate/key and enter new password.
          
---------

#RO

== Instalare ==

1. Încărcați folderul ```modules``` în mapa rădăcină a platformei WHMCS
2. WHMCS 7: Activați pluginul: 'Setup->Payments->Payment Gateways' și efectuați setările necesare.
   
   WHMCS 8: Activați pluginul: 'Addons->Apps & Integrations->Browse->Payments->maib' și efectuați setările necesare.

---------

#RU

== Установка ==

1. Загрузите папку ```modules``` в корневую директорию WHMCS
2. WHMCS 7: Активируйте плагин в меню 'Setup->Payments->Payment Gateways' и сделайте нужные настройки

   WHMCS 8: Активируйте плагин в меню 'Addons->Apps & Integrations->Browse->Payments->maib' и сделайте нужные настройки
