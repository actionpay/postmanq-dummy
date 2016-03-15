# postmanq-dummy

Небольшой сервис заглушка для создания тестовых локаций и замены там PostmanQ

## установка

устанавливаем [Composer](https://getcomposer.org/)

затем устанавливаем

    pecl install mailparse       #PHP Version = 7
	pecl install mailparse-2.1.6 #PHP Version < 7
	apt-get install php5-imap
    php composer.phar update
    
## использование

в первый раз необходимо запустить php www/refresh.php

после этого выполнения скрипта получим такую файловую структуру:

    /
        www/
            index.html
            mail/
                mail1.html
                mail2.html
                mail3.html
            
в последующие разы список можно обновлять через скрипт или прямо на страничке с письмами (для этого необходимо дать права на index.html и папку mail)


    
	
