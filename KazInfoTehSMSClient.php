<?php

class KazInfoTehSMSClient
{
    private $username;
    private $password;
    private $from;
    private $url;

    public function __construct(
        $username,
        $password,
        $url = 'http://isms.center/api/sms',
        $from = 'KiT_Notify'
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->url = $url;
        $this->from = $from;
    }

    protected function jsonUnescapeUnicode($json)
    {
        $unicodeToChar = function ($matches) {
            $entity_code = '&#' . hexdec($matches[1]) . ';';
            return html_entity_decode($entity_code, ENT_COMPAT, 'UTF-8');
        };

        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', $unicodeToChar, $json);
    }

    public function request($route, $data = null, $method = 'POST')
    {
        if($data) {
            $data = json_encode($data);
            $data = $this->jsonUnescapeUnicode($data);
        }

        $params = array(
            'http' => array(
                'method' => $method,
                'header' => "Authorization: Basic "
                    . base64_encode($this->username . ':' . $this->password) . "\r\n"
                    . "cache-control: no-cache\r\n"
                    . "Content-Type: application/json\r\n",
                'content' => $data
            )
        );

        $context = stream_context_create($params);

        $currentReporting = error_reporting();
        error_reporting(0);
        $result = file_get_contents($this->url . $route, false, $context);
        error_reporting($currentReporting);

        $headers = $http_response_header;
        preg_match('/\b\d{3}\b/', $headers[0], $matches);
        $httpCode = $matches[0];

        if (substr($result, 0, 1) === '{' || substr($result, 0, 1) === '[') {
            return array('httpCode' => $httpCode, 'data' => $this->jsonUnescapeUnicode($result));
        }

        return array('httpCode' => $httpCode, 'data' => null);
    }

    public function send(
        $to,
        $text,
        $flash = false,
        $sentAt = null,
        $notifyUrl = null,
        $extraId = null,
        $callback_data = null
    ) {
        $data = array(
            'from' => $this->from,
            'to' => $to,
            'text' => urldecode($text),
            'flash' => $flash,
            'sent_at' => $sentAt,
            'notify_url' => $notifyUrl,
            'extra_id' => $extraId,
            'callback_data' => $callback_data
        );

        return $this->request('/send', $data);
    }

    public function sendBulk($data)
    {
        return $this->request('/send/bulk', $data);
    }

    public function reportByMessageId($messageId)
    {
        return $this->request('/report?message_id=' . $messageId);
    }

    public function reportByExtraId($extraId)
    {
        return $this->request('/report?extra_id=' . $extraId);
    }

    public function reportByBulkId($bulkId)
    {
        return $this->request('/report?bulk_id=' . $bulkId);
    }

    public function reportByPeriod($dateTimeFrom, $dateTimeTo) {
        $date = $dateTimeFrom . '/' . $dateTimeTo;
        return $this->request('/report?period=' . $date);
    }
}
