<?php


namespace DBProxy;


use Medoo\Medoo;
use Microservices\Microservice;

/**
 * Описывает класс, создающий объект подключения к базе данных
 */
class DbProxy
{
    /**
     * Загружает настройки подключения к базе данных.
     * Создаёт объект подключения к базе данных в переданном сервисе.
     *
     * @param Microservice $service
     */
    public static function createDataBaseConnection($service)
    {
        $serviceDirPath = $service->getServiceDirPath();
        $databaseConfigFile = 'db.php';
        $databaseConfigPath = $serviceDirPath . '/' . $databaseConfigFile;

        $service->db = false;
        if (file_exists($databaseConfigPath)) {
            $databaseSettings = include $databaseConfigPath;
            if (!empty($databaseSettings)) {
                $service->db = new Medoo($databaseSettings);
            }
        }

    }
}