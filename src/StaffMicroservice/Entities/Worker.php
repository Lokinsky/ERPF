<?php


namespace StaffMicroservice\Entities;


use Microservices\DataObjects\ArrayObject;
use StaffMicroservice\Staff;
use StaffMicroservice\Validator;

/**
 * Класс, описывающий сущность Работник
 */
class Worker extends ArrayObject
{
    /**
     * id пользователя, которому принадлежит учётная запись
     * @var int
     */
    public $userId;

    /**
     * Имя работника
     * @var string
     */
    public $name;

    /**
     * Фамилия работника
     * @var string
     */
    public $surname;

    /**
     * Отчество работника
     * @var string
     */
    public $patronymic;

    /**
     * Json массив телефонов работника
     * @var string
     */
    public $phones;

    /**
     * Json массив ссылок работника
     * @var string
     */
    public $links;

    /**
     * Json массив скилов работника
     * @var string
     */
    public $skills;

    /**
     * Json массив дополнительных значений
     * @var string
     */
    public $attachments;

    /**
     * Метка времени создания работника
     * @var int
     */
    public $createdAt;

    /**
     * id работника
     * @var int
     */
    protected $id;

    /**
     * Метка времени удаления работника
     * @var null|int
     */
    protected $deletedAt;


    /**
     * Возможность наполнения объекта при создании.
     * @param array $from
     */
    public function __construct($from = [])
    {
        if (!empty($from)) $this->pull($from);
    }


    /**
     * Создание записи о работнике в БД из собственных полей
     * @return bool|int
     */
    public function create()
    {
        if (Validator::validate($this, ['workerCreate'])) {
            $this->createdAt = time();
//            var_dump($this->getFields());
            if (!empty(Staff::getInstance()->db->insert('workers', $this->getFields()))) {
                return Staff::getInstance()->db->id();
            }
        }

        return false;
    }

    /**
     * Возвращает массив данных о работнике по его id в случае успеха
     * @param $id int
     * @return bool|mixed
     */
    public function getById($id)
    {
        $found = Staff::getInstance()->db->get('workers', '*', ['id' => $id]);
        return $found;
    }

    public function getByUserId($id){
        $found = Staff::getInstance()->db->get('workers', '*', ['userId' => $id]);
        return $found;
    }

    /**
     * Геттер для id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Массив псевдонимов полей
     * @return array|\string[][]
     */
    public function getFieldsAliases()
    {
        return [
            'id' => ['id', 'workerId'],
            'userId' => ['userId', 'user'],
            'name' => ['name'],
            'surname' => ['surname'],
            'patronymic' => ['patronymic', 'patron'],
            'phones' => ['phones'],
            'links' => ['links'],
            'skills' => ['skills'],
            'attachments' => ['attaches', 'attachments'],
        ];
    }
}