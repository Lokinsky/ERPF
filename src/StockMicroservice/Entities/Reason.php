<?php


namespace StockMicroservice\Entities;


use Microservices\DataObjects\ArrayObject;
use Microservices\DataObjects\Model;

class Reason extends Model
{
    public $name;
    public $description;
    public $createdAt;

    /**
     * Заполнение полей при создании экземпляра
     * @param array|ArrayObject $fields
     */
    public function __construct($fields=[])
    {
        if(is_object($fields)) $fields = $fields->getFields();
        if(!empty($fields)) $this->pull($fields);
    }

    /**
     * Расширение уникальных полей при получении
     * @param array $where
     * @return bool|mixed
     */
    public function get($where = [])
    {
        if(empty($where)){
            $where = [
                'OR' => [
                    'id' => $this->getId(),
                    'name' => $this->getName(),
                ]
            ];
        }

        return parent::get($where);
    }

    /**
     * Расширнеи уникальных полей при обновлении
     * @param array $fields
     * @param array $where
     * @return bool
     */
    public function update($fields = [], $where = [])
    {
        if(empty($where)){
            $where = [
                'OR' => [
                    'id' => $this->getId(),
                    'name' => $this->getName(),
                ]
            ];
        }

        return parent::update($fields, $where);
    }

    /**
     * Расширение уникальных полей при удалении
     * @param array $where
     * @return bool
     */
    public function delete($where=[])
    {
        if(empty($where)){
            $where = [
                'OR' => [
                    'id' => $this->getId(),
                    'name' => $this->getName(),
                ]
            ];
        }
        return parent::delete($where);
    }

    /**
     * Геттер для имени
     * @return string|null
     */
    public function getName(){
        return $this->name;
    }
}