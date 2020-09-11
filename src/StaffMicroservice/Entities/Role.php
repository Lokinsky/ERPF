<?php


namespace StaffMicroservice\Entities;


use Microservices\DataObjects\ArrayObject;
use StaffMicroservice\Staff;
use StaffMicroservice\Validator;

/**
 * Класс, описывающий сущность Роль
 */
class Role extends ArrayObject
{
    /**
     * Название роли
     * @var string
     */
    public $name;

    /**
     * Описание роли
     * @var string
     */
    public $description;

    /**
     * Метка времени создания роли
     * @var int
     */
    public $createdAt;

    /**
     * id роли
     * @var int
     */
    protected $id;

    /**
     * Метка времени удаления роли
     * @var int|null
     */
    protected $deletedAt;

    /**
     * Возможность наполнения при создании экземпляра
     * @param array $from
     */
    public function __construct($from = [])
    {
        if (!empty($from)) $this->pull($from);
    }

    /**
     * Создание записи в БД по собственным полям
     * @return bool|int
     */
    public function create()
    {
        if (Validator::validate($this, ['roleCreate'])) {
            $this->createdAt = time();
            if (!empty(Staff::getInstance()->db->insert('roles', $this->getFields()))) {
                return Staff::getInstance()->db->id();
            }
        }

        return false;
    }

    /**
     * Геттер для id
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Псевдонимы для полей
     * @return array|\string[][]
     */
    public function getFieldsAliases()
    {
        return [
            'id' => ['id'],
            'name' => ['name'],
            'description' => ['desc', 'description'],
        ];
    }
}