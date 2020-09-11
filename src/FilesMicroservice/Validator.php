<?php


namespace FilesMicroservice;


use Validators\BasicValidator;

class Validator extends BasicValidator
{

    public static function getRules()
    {
        return [
            'saveByUrl' => [
                'url' => ['url', 'ext', ['lmax' => 512]],
            ],
            'fileCreate' => [
                'name' => [['lmax' => 64]],
                'description' => [['lmax' => 256]],
            ],
            'acceptFile' => [
                'name' => ['ext', ['lmax' => 512]],
                'size' => [['rmax' => 52428800]],
            ]
        ];
    }


    public static function ruleExt($input)
    {
        $path = static::getUrlPath($input);
        $extension = static::getFileExtension($path);
        if (!empty($extension)) {
            $extension = mb_strtolower($extension);
            $allowed = static::getAllowedExtensions();
            if (!empty($allowed)) {
                if (!in_array($extension, $allowed)) return false;
            }

            $forbidden = static::getForbiddenExtensions();
            if (!empty($forbidden)) {
                if (in_array($extension, $forbidden)) return false;
            }
        }

        return true;
    }

    public static function getUrlPath($url)
    {
        return parse_url($url, PHP_URL_PATH);
    }

    public static function getFileExtension($filePath)
    {
        return pathinfo($filePath, PATHINFO_EXTENSION);
    }

    public static function getAllowedExtensions()
    {
        return [

        ];
    }

    public static function getForbiddenExtensions()
    {
        return [
            'bat',
            'chm',
            'com',
            'eml',
            'exe',
            'hta',
            'htm',
            'html',
            'js',
            'pif',
            'reg',
            'scr',
            'shb',
            'sms',
            'url',
            'vbs',
            'php',
            'asp',
            'py',
            'phtml',
            'xml',
            'inc',
            'pl',
            'jsp',
            'shtml',
            'sh',
            'cgi',
        ];
    }
}