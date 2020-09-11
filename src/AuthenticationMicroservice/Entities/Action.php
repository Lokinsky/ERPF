<?php


namespace AuthenticationMicroservice\Entities;


//use AuthenticationMicroservice\Authentication;
//use AuthenticationMicroservice\Validator;
use Microservices\DataObjects\Model;


/**
 * Представляет собой сущность базы данных для действия, характеризующего выполнимый метод апи
 */
class Action extends Model
{
    /**
     * Строка дейсвтия с учётом микросервиса, в пространстве которого оно выполняется.
     * @var string
     */
    public $value;

    /**
     * Дата создания
     * @var int
     */
    public $createdAt;

    /**
     * Дата удаления
     * @var null|int
     */
    public $deletedAt;

    /**
     * id действия
     * @var int
     */
//    protected $id;

    /**
     * Возможность наполения напрямую при создании
     * @param array $from
     */
    public function __construct($from = [])
    {
        if (!empty($from)) $this->pull($from);
    }


    /**
     * Возвращает псевдонимы полей
     * @return array|\string[][]
     */
    public function getFieldsAliases()
    {
        return [
            'id' => ['id'],
            'value' => ['value', 'val'],
            'createdAt' => ['createdAt'],
        ];
    }

//    /**
//     * Пытается сохранить свои поля в базе данных
//     * @return bool|int
//     */
//    public function create()
//    {
//        if (Validator::validate($this, ['actionCreate'])) {
//            $this->createdAt = time();
//            if (!empty(Authentication::getInstance()->db->insert('actions', $this->getFields()))) {
//                return Authentication::getInstance()->db->id();
//            }
//        }
//
//        return false;
//    }
}