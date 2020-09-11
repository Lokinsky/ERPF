<?php


namespace AuthenticationMicroservice\Entities;


use AuthenticationMicroservice\Authentication;
use Microservices\DataObjects\Model;

//use Validators\ValidatorVisitor;

/**
 * Описывает класс сущности Пользователь, которая является основной при аутентификации
 */
class User extends Model
{
    /**
     * Логин пользователя
     * @var string
     */
    public $login;

    /**
     * Хэш пароля пользователя
     * @var string
     */
    public $hash;

    /**
     * Email пользователя
     * @var string
     */
    public $email;

    /**
     * Имя пользователя
     * @var string
     * @deprecated
     */
    public $name;

    /**
     * Фамилия пользователя
     * @var string
     * @deprecated
     */
    public $surname;

    /**
     * Отчество пользователя
     * @var string
     * @deprecated
     */
    public $patronymic;

    /**
     * Метка времени создания пользователя
     * @var int
     */
    public $createdAt;

    /**
     * Метка времени удаления пользователя
     * @var null|int
     */
    public $deletedAt;

    /**
     * id пользователя
     * @var int
     */
//    protected $id;

    /**
     * Пароль пользователя, не сохраняется в БД
     * @var string
     */
    protected $password;


    /**
     * Возможность наполнения при создании + установка метки времени создания
     * @param array $fields
     */
    public function __construct($fields = [])
    {
        if (!empty($fields)) $this->pull($fields);
    }

    /**
     * Наполнение полей объекта из массива с учётом обновления хэша пароля
     * @param array $fromArray
     */
    public function pull(&$fromArray)
    {
        parent::pull($fromArray);
        $this->refreshHash();
    }

    /**
     * Обновляет хэш пароля пользователя
     */
    public function refreshHash()
    {
        if (empty($this->hash) and !empty($this->password)) {
            $this->hash = Authentication::hashPassword($this->password);
        }
    }

//    /**
//     * Возвращает объёкт пользователя с полями из БД
//     * @param $id int
//     * @return User
//     */
//    public static function getById($id)
//    {
//        return (new User(Authentication::getInstance()->db->get('users', '*', ['id' => $id])));
//    }

    /**
     * Возвращает массив псевдонимов для полей
     * @return array|\string[][]
     */
    public function getFieldsAliases()
    {
        return [
            'id' => ['id'],
            'login' => ['login', 'log', 'nickname', 'nick'],
            'hash' => ['hash'],
            'password' => ['pass', 'password', 'pwd'],
            'email' => ['email'],
            'name' => ['name', 'firstName', 'n'],
            'surname' => ['surname', 'lastName'],
        ];
    }

    /**
     * Генерирует строку Cookie для пользователя
     * @return bool|string
     */
    public function genCookie()
    {
        $cookieFields = $this->createCookieFields();
        return Authentication::getInstance()->encryptData(json_encode($cookieFields));
    }

    /**
     * Подготавливает масив данных для Cookie
     * @return array
     */
    public function createCookieFields()
    {
        $cookieFields = $this->getFields();
        if (!isset($cookieFields['id'])) $cookieFields['id'] = $this->id;
        if (isset($cookieFields['hash'])) unset($cookieFields['hash']);

        return $cookieFields;
    }

    /**
     * Пытается расшифровать Cookie и наполнить себя данными из его полей
     * @param $cookie
     * @return $this|bool
     */
    public function decryptCookie($cookie)
    {
        if (!is_string($cookie)) return false;
        $decrypt = Authentication::getInstance()->decryptData($cookie);
        if (empty($decrypt)) return false;

        $userFields = json_decode($decrypt, true);
        if (empty($userFields)) return false;

        $this->pull($userFields);

        return $this;
    }

//    /**
//     * Геттер для id
//     * @return int
//     */
//    public function getId()
//    {
//        if (empty($this->id)) return 0;
//        return $this->id;
//    }

    /**
     * Сеттер для id
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Геттер для пароля
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function get($where = [], $what = '*')
    {
        if(empty($where)){
            $where = [
                'OR' => [
                    'id' => $this->getId(),
                    'login' => $this->login,
                    'email' => $this->email,
                ]
            ];
        }
        return parent::get($where, $what);
    }

    public function exists($where = [])
    {
        if(empty($where)){
            $where = [
                'OR' => [
                    'login' => $this->login,
                    'email' => $this->email
                ]
            ];
        }
        return parent::exists($where);
    }

    /**
     * Сеттер для пароля
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Вовращает массив данных о пользователе по его токену, если находит.
     * @param string $token
     * @return bool|mixed
     */
    public function getInfoByToken($token)
    {
        $user = $this->db->get("tokens", [
            "[>]users" => ["userId" => 'id'],
        ], [
            'users.id',
            'users.login',
            'users.hash',
            'users.email',
            'users.name',
            'users.surname',
            'users.createdAt',
            'tokens.id(tokenId)',
            'tokens.createdAt(tokenCreatedAt)',
            'tokens.duration(tokenDuration)',
        ], [
            'token' => $token
        ]);

        return $user;
    }

}