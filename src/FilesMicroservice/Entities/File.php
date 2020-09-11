<?php


namespace FilesMicroservice\Entities;


use FilesMicroservice\Files;
use FilesMicroservice\Validator;
use Microservices\DataObjects\ArrayObject;

class File extends ArrayObject
{
    protected $createdAt;
    protected $file;

    public function __construct($from)
    {
        if (!empty($from)) $this->pull($from);
        $this->createdAt = time();
    }

    public function getFieldsAliases()
    {
        return [
            'name' => ['name'],
            'description' => ['description', 'desc'],
        ];
    }

    public function save()
    {
        if (Validator::validate($this, ['fileCreate'])) {
            $file = $this->findByFile();

            if (!empty($file)) return $file['id'];

            if (!empty(Files::getInstance()->db->insert('files', $this->getFields()))) {
                return Files::getInstance()->db->id();
            }
        }

        return false;
    }

    public function findByFile($file = false)
    {
        if (empty($file)) $file = $this->getFile();

        return Files::getInstance()->db->get('files', '*', ['file' => $file]);
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getFields()
    {
        return get_object_vars($this);
    }
}