<?php


namespace AuthenticationMicroservice\Entities;


//use AuthenticationMicroservice\Authentication;
//use AuthenticationMicroservice\Validator;
use Microservices\DataObjects\ExpiringModel;

/**
 * Описывает сущность закона, который однозначно определяет, к какому действию у какого пользователя есть доступ.
 */
class Law extends ExpiringModel
{
    /**
     * id пользователя, на которого действует закон
     * @var int
     */
    public $userId;

    /**
     * id действия, на которое действует закон
     * @var int
     */
    public $actionId;

    /**
     * Метка времени создания закона
     * @var int
     */
    public $createdAt;

    /**
     * Количество секунд, на протяжении которых действует закон; 0 без ограничений
     * @var int
     */
    public $duration = 0;

    /**
     * Метка времени удаления закона
     * @var null|int
     */
    public $deletedAt;

    /**
     * id закона
     * @var int
     */
//    protected $id;


    /**
     * Возможность наполнения при создании
     * @param array $from
     */
    public function __construct($from = [])
    {
        if (!empty($from)) $this->pull($from);
    }

    /**
     * Проверяет, разрешено выполнить данное действие данному пользователю
     * @param int $userId
     * @param string $action
     * @return bool
     */
    public function access($userId, $action)
    {
        if ($userId == 1) return true;

        $action = $this->db->get('actions', 'id', ['value' => $action]);

        if (!empty($action)) {
            $justice = $this->db->get('laws', ['id', 'createdAt', 'duration'], [
                'AND' => [
                    'userId' => $userId,
                    'actionId' => $action,
                ],
            ]);

            if (!empty($justice)) {
                $law = new Law($justice);
                return $law->checkDuration();
            }
        }

        return false;
    }

//    /**
//     * Метод проверки срока длительности закона
//     * @param int $created
//     * @param int $duration
//     * @return bool
//     */
//    protected function checkDuration($created, $duration)
//    {
//        if ($duration == 0) return true;
//        $now = time();
//
//        return (($created + $duration) > $now);
//    }

//    /**
//     * Метод создания записи в БД из полей закона
//     * @return bool|int
//     */
//    public function create()
//    {
//        if (Validator::validate($this, ['lawCreate'])) {
//            $this->createdAt = time();
//            if (!empty(Authentication::getInstance()->db->insert('laws', $this->getFields()))) {
//                return Authentication::getInstance()->db->id();
//            }
//        }
//
//        return false;
//    }

}