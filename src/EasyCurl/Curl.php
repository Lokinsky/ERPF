<?php


namespace EasyCurl;


/**
 * Описывает класс с простейшим функционалом работы через Curl
 */
class Curl
{
    /**
     * Отправляет GET запрос на url
     * @param $url string
     * @param int $jsn_decode
     * @return bool|array|string
     */
    public static function get($url, $jsn_decode = 0)
    {
        $timeOut = 60;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($curl);
        curl_close($curl);
        if ($out) {
            if ($jsn_decode == 1) {
                $out = json_decode($out, true);
            }
            return $out;
        } else {
            return false;
        }
    }

    /**
     * Выполняет POST запрос на url передавая массив с данными
     * @param string $url
     * @param array $post
     * @param int $jsn_decode
     * @return bool|array|string
     */
    public static function post($url, $post, $jsn_decode = 1)
    {
        $timeOut = 60;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        $out = curl_exec($curl);
        curl_close($curl);
        if ($out) {
            if ($jsn_decode == 1) {
                $out = json_decode($out, true);
            }
            return $out;
        } else {
            return false;
        }
    }

    /**
     * Пытается скачать файл по ссылке и сохранить его локально
     * @param string $from
     * @param string $to
     * @return mixed
     */
    public static function download($from, $to)
    {
        $file = fopen($to, 'w+');
        $timeOut = 60;

        $curl = curl_init($from);
        curl_setopt($curl, CURLOPT_FILE, $file);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        fclose($file);

        return $statusCode;
    }
}