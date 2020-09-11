<?php


namespace AuthenticationMicroservice\Entities;


use AuthenticationMicroservice\Authentication;
use AuthenticationMicroservice\Validator;
use Microservices\DataObjects\ExpiringModel;

/**
 * Класс, описывающий сущность ключа доступа для авторизации
 */
class Token extends ExpiringModel
{
    /**
     * id пользователя, для которого выдаётся токен
     * @var int
     */
    public $userId;

    /**
     * Строка ключа доступа
     * @var string
     */
    public $token;

    /**
     * Длительность действия ключа доступа
     * @var int
     */
    public $duration = 0;

    /**
     * Дата создания ключа доступа
     * @var int
     */
    public $createdAt;

    /**
     * id ключа домтупа
     * @var int
     */
//    protected $id;

    /**
     * Возможность наполнения при непосредственном создании экземпляра
     * @param array $from
     */
    public function __construct($from = [])
    {
        if (!empty($from)) $this->pull($from);
    }

    /**
     * Создаёт ключ доступа на основе указанных полей и добавляет его в БД
     * @return bool|string
     */
    public function generate()
    {
        if (empty($this->userId)) return false;

        $user = User::getById($this->userId);
        if (!empty($user)) {
            $this->token = md5(microtime()) . md5($user->hash);

            if (Validator::validate($this, ['tokenCreate'])) {
                $this->createdAt = time();
                if ($this->db->insert('tokens', $this->getFields())) {
                    return $this->token;
                }
            }
        }

        return false;
    }


    /**
     * Возвращает массив псевдонимов для полей
     * @return array|\string[][]
     */
    public function getFieldsAliases()
    {
        return [
            'userId' => ['id', 'userId', 'user'],
            'token' => ['token', 'key', 'access_key', 'access_token'],
            'duration' => ['duration', 'dur'],
        ];
    }
}