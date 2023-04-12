# KazInfoTehSMSClient
Класс для работы с SMS-рассылками [KazInfoTeh](https://kazinfoteh.kz)

- работает на PHP 5.2 и выше
- не требует нестандартных модулей

```
require_once('KazInfoTehSMSClient.php');

$client = new KazInfoTehSMSClient('username', 'password');

// отправка
$result = $client->send('+77777777777', 'Тест');

// отчет
$result = $client->reportByMessageId('404');

// в результате вернется массив 
// с httpCode (код ответа) 
// и data (ответ от сервиса в формате json)
```
