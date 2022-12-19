# maib Payment Gateway for WHMCS

#EN

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

Test card:

      Card number: 5102180060101124 
      Exp. date: 06/28 
      CVV: 760


***Callback*** - Send this URL and website IP to **maib** ecommerce support.

***Certificate*** - Path to certificate for merchant authorization. By default path to test certificate. 

***Key certificate*** - Path to key certificate. By default path to test key certificate. 

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

Acest modul integrează **maib** ecommerce cu platforma WHMCS.

**CERINȚE**

CURL

**ÎNAINTE DE UTILIZARE**

Pentru a putea efectua transmiteți un email către suportul **maib** ecommerce (ecom@maib.md) care să conțină ***IP*** extern al website-ului și ***Callback URL***.

**INSATALRE**

1. Încărcați folderul ```modules``` în mapa rădăcină a platformei WHMCS
2. WHMCS 7: Activați pluginul: 'Setup->Payments->Payment Gateways' și efectuați setările necesare.
   
   WHMCS 8: Activați pluginul: 'Addons->Apps & Integrations->Browse->Payments->maib' și efectuați setările necesare.

**CONFIGURARE**

***Display Name*** - Denumirea metodei de plată cum o văd clineții pe website.

***Test Mode*** - Biați pentru a activa modul de testare. Următoarele teste sunt obligatorii: achitare, restituire plată și închiderea zilei.

Card pentru efectuarea testelor:

      Card number: 5102180060101124 
      Exp. date: 06/28 
      CVV: 760

***Callback*** - Transmiteți acest URL și IP-ul către **maib** ecommerce suport.

***Certificate*** - Calea spre certificat pentru autorizare comerciant. În mod implicit, calea către certificatul pentru teste.

***Key certificate*** - Calea spre cheia certificatului. În mod implicit, calea către cheia certificatului pentru teste.

***Password*** - Parola certificatului. Parola certificatului pentru teste: Za86DuC

***Closing of business day*** -  URL închidere zi. Executați acest URL prin CRON în fiecare zi la ora 23:59!

---------

#RU



== Установка ==

1. Загрузите папку ```modules``` в корневую директорию WHMCS
2. WHMCS 7: Активируйте плагин в меню 'Setup->Payments->Payment Gateways' и сделайте нужные настройки

   WHMCS 8: Активируйте плагин в меню 'Addons->Apps & Integrations->Browse->Payments->maib' и сделайте нужные настройки
