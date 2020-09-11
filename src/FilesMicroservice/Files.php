<?php


namespace FilesMicroservice;


use AuthenticationMicroservice\Authentication;
use EasyCurl\Curl;
use FilesMicroservice\Entities\File;
use Microservices\Answers\Answer;
use Microservices\DataObjects\ArrayObject;
use Microservices\Microservice;
use Microservices\Questions\Question;

/**
 * Описывает микросервис управляющий хранением файлов
 */
class Files extends Microservice
{

    /**
     * Обеспечивает создание необходимых директорий
     */
    public function __construct()
    {
        parent::__construct();
        $this->provideTempDir();
        $this->provideFilesDir();
    }

    /**
     * Создаёт директорию для временных файлов, если её нет
     */
    public function provideTempDir()
    {
        $tempDir = $this->getTempDirPath();
        if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);
    }

    /**
     * Воввращет путь к временной директории
     * @return string
     */
    public function getTempDirPath()
    {
        return $this->getServiceDirPath() . '/temp';
    }

    /**
     * Создаёт директорию для хранения файлов, если её нет
     */
    public function provideFilesDir()
    {
        $filesDir = $this->getFilesDirPath();
        if (!file_exists($filesDir)) mkdir($filesDir, 0777, true);
    }

    /**
     * Вовзращает диреткорию для хранения файлов
     * @return string
     */
    public function getFilesDirPath()
    {
        return $this->getServiceDirPath() . '/files';
    }

    /**
     * Метод, сохраняющий файл по ссылке
     * @param Question $question
     * @return Answer
     */
    public function apiSaveFileByUrl(Question $question)
    {
        $answer = new Answer();

        if (empty($question->url) or !Validator::validate($question, 'saveByUrl')) return $answer->genError('Error: url validation failed');

        $file = $this->downloadFileByUrl($question->url);

        if (empty($file)) return $answer->genError('Error: failed to download file');


        return $this->saveFile($question, $file, $answer);
    }

    /**
     * Скачивает файл по ссылке
     * @param $url
     * @return bool|string
     */
    public function downloadFileByUrl($url)
    {
        $filePath = $this->getTempFilePath();

        $urlFilePath = Validator::getUrlPath($url);
        $extension = Validator::getFileExtension($urlFilePath);

        $downloadCode = Curl::download($url, $filePath);
        if ($downloadCode != 200 or filesize($filePath) == 0) return false;

        $fileName = $this->genFileName($filePath, $extension);

        $newFilePath = $this->getFilesDirPath() . '/' . $fileName;
        rename($filePath, $newFilePath);

        return $fileName;
    }

    /**
     * Возвращает путь до файла во временной директории
     * @return string
     */
    public function getTempFilePath()
    {
        return $this->getTempDirPath() . '/' . $this->getTempFilename();
    }

    /**
     * Создаёт имя файла для временного хранения
     * @return string
     */
    public function getTempFilename()
    {
        return microtime() . '.temp';
    }

    /**
     * Генерирует имя файла
     * @param string $tempFilePath
     * @param string $extension
     * @return string
     */
    public function genFileName($tempFilePath, $extension)
    {
        return md5_file($tempFilePath) . '.' . $extension;
    }

    /**
     * Сохраняет запись о файле в БД
     * @param Question $question
     * @param string $fileName
     * @param Answer $answer
     * @return mixed
     */
    public function saveFile($question, $fileName, $answer)
    {
        $dbFile = new File($question->getFields());
        $dbFile->setFile($fileName);
        $fileId = $dbFile->save();

        if (empty($fileId)) return $answer->genError('Error: failed file create');

        $answer->filePath = $this->createPublicFilePath($fileName);

        $owner = Authentication::getInstance()->getCurrentUser();
        if (empty($owner)) {
            $ownerId = 0;
        } else {
            $ownerId = $owner->getId();
        }

        if (empty($this->db->insert('owners', [
            'ownerId' => $ownerId,
            'fileId' => $fileId,
            'createdAt' => time(),
        ]))) return $answer->genError('Error: failed to attach owner to file');
        $answer->owner = $ownerId;

        return $answer;
    }

    /**
     * Создаёт путь к файлу для доступа
     * @param $file string
     * @return string
     */
    public function createPublicFilePath($file)
    {
        return $this->getFilesDirPath() . '/' . $file;
    }

    /**
     * Метод, сохраняющий файл, переданный в POST
     * @param Question $question
     * @return Answer
     */
    public function apiSaveFile($question)
    {
        $answer = new Answer();

        if (empty($_FILES) or !Validator::ruleCmax($_FILES, 1)) return $answer->genError('Error: validation failed');

        foreach ($_FILES as $secondFileName => $file) {
            $arObj = new ArrayObject();
            $arObj->pull($file);
            if (!Validator::validate($arObj, 'acceptFile')) $answer->genError('Error: failed file validation');

            $filePath = $this->getTempFilePath();
            $extension = Validator::getFileExtension($file['name']);

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                $answer->genError('Error: bad file');
                continue;
            }

            $fileName = $this->genFileName($filePath, $extension);
            $newFilePath = $this->getFilesDirPath() . '/' . $fileName;
            if (!rename($filePath, $newFilePath)) $answer->genError('Error: failed to make file public');

            return $this->saveFile($question, $fileName, $answer);
        }

        return $answer;
    }


}