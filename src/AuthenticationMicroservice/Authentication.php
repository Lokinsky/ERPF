<?php


namespace AuthenticationMicroservice;


use AuthenticationMicroservice\Entities\Action;
use AuthenticationMicroservice\Entities\Law;
use AuthenticationMicroservice\Entities\Token;
use AuthenticationMicroservice\Entities\User;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Microservices\Answers\Answer;
use Microservices\Microservice;
use Microservices\Questions\Question;
use Microservices\Requests\Request;

//use Validators\ValidatorVisitor;

class Authentication extends Microservice implements AuthI
{
    protected $cryptoKey;
    protected $currentUser;

    /**
     * Authentication constructor.
     * Наследует конструктор Microservice и обеспечивает генерацию ключа шифрования
     *
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function __construct()
    {
        parent::__construct();
        $this->provideCryptoKey();
    }

    /**
     * Проверяет, сущестрвует ли сгенерирированный ключ шифрования, если не находит генерирует новый
     *
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function provideCryptoKey()
    {
        $keyPath = $this->getCryptoKeyPath();

        if (file_exists($keyPath) === false or filesize($keyPath) === 0) {
            $newAsciiKey = Key::createNewRandomKey()->saveToAsciiSafeString();

            $fh = fopen($keyPath, 'w');
            fwrite($fh, $newAsciiKey);
            fclose($fh);

            $this->cryptoKey = $newAsciiKey;
        } else {
            $asciiKey = file_get_contents($keyPath);
            if (!empty($asciiKey)) $this->cryptoKey = $asciiKey;
        }
    }

    /**
     * Возвращает путь до файла с ключом шифрования
     *
     * @return string
     */
    public function getCryptoKeyPath()
    {
        return $this->getServiceDirPath() . '/cryptokey';
    }

    /**
     * Возвращает хэш пароля
     *
     * @param string $password
     * @return false|string|null
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Шифрует входящую строку и возвращает результат
     *
     * @param string $data Строка для шифрования
     * @return bool|string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function encryptData($data)
    {
        if (!empty($this->cryptoKey)) {
            $key = Key::loadFromAsciiSafeString($this->cryptoKey);
            return Crypto::encrypt($data, $key);
        }

        return false;
    }

    /**
     * Пробует дешифровать строку и возвращает результат
     *
     * @param string $cdata Зашифрованная строка
     * @return bool|string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function decryptData($cdata)
    {
        try {
            if (empty($this->cryptoKey)) return false;
            $key = Key::loadFromAsciiSafeString($this->cryptoKey);
            return Crypto::decrypt($cdata, $key);
        } catch (WrongKeyOrModifiedCiphertextException $ex) {
//            return false;
        }

        return false;
    }

    /**
     * Метод авторизации пользователя по логину и паролю
     * @param Question $question
     * @return Answer
     */
    public function apiLogin(Question $question)
    {
        $user = new User($question->getFields());
        $answer = new Answer();

        if (!Validator::validate($user, 'userLogin')) return $answer->genError('Error: failed validation');
        $user->setDb($this->db);

        if (!empty($user->login) or !empty($user->email)) {
            $foundUser = $user->get();

            if (empty($foundUser)) {
                $answer->genError('Error: user doesn`t exist');
            } else {
                $verify = Authentication::cmpPasswordHash($user->getPassword(), $foundUser['hash']);
                if ($verify) {
                    $answer->success = true;
                    $answer->userId = $foundUser['id'];
                    $answer->cookies = $this->rememberUser(new User($foundUser));
                } else {
                    $answer->genError('Error: incorrect password');
                }
            }
        } else {
            $answer->genError('Error: bad user data');
        }

        return $answer;
    }

    /**
     * Проверяет соотвествие пароля хешу
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function cmpPasswordHash($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Запоминает текущего пользователя, сохраняя часть данных о нём в зашифрованном виде в cookies
     *
     * @param User $user
     * @param int $duration
     * @return array Массив для cookies
     */
    public function rememberUser($user, $duration = 24 * 60 * 60)
    {
//        list($x1,$x2)=explode('.',strrev($_SERVER['HTTP_HOST']));
//        $domain = $x1.'.'.$x2;

        $expiredAt = time() + $duration;
        $cookie = $user->genCookie();
        setcookie('au', $cookie, $expiredAt, '/');

        return ['au' => $cookie];
    }

    /**
     * Метод регистрации нового пользователя
     * @param Question $question
     * @return Answer
     */
    public function apiRegister($question)
    {
        $user = new User($question->getFields());
        $answer = new Answer();

        if (Validator::validate($user, 'userRegister')){
            $user->setDb($this->db);
            $findNick = $user->exists();
            if (empty($findNick)) {
                $answer->user = $user->create();
//                if (!empty($this->db->insert('users', $user->getFields()))) $answer->userId = $this->db->id();
            } else {
                $answer->genError('Error: user already exists');
            }
        } else {
            $answer->genError('Error: bad user data');
        }

        return $answer;
    }

    /**
     * Метод изменения пароля пользователя
     * @param Question $question
     * @return Answer
     */
    public function apiChangePassword($question)
    {
        $user = $this->getCurrentUser();
        $answer = new Answer();
        if (empty($user)) return $answer->genError('Error: need authorize');
        if (!Validator::validate($question, 'changePassword')) return $answer->genError('Error: failed validation');
        $user->setDb($this->db);
        $foundUser = $user->get();

        if (empty($foundUser)) {
            $answer->genError('Error: user doesn`t exist');
        } else {
            if (empty($question->newPassword)) return $answer->genError('Error: need new password');
            $user->setPassword($question->newPassword);
            $user->refreshHash();
            if (!empty($user->getPassword())) {
                $update = $this->db->update('users', ['hash' => $user->hash], [
                    'id' => $foundUser['id'],
                ]);

                if (!empty($update) and $update->rowCount() > 0) {
                    $answer->success = true;
                    $answer->userId = $foundUser['id'];
                } else {
                    $answer->genError('Error: failed update');
                }
            }
        }

        return $answer;
    }

    /**
     * Возвращает текущего авторизованного пользователя или false, если его нет
     *
     * @param Request $request
     * @return User|bool
     */
    public function getCurrentUser()
    {
        if (!empty($this->currentUser)) {
            return $this->currentUser;
        }

        return false;
    }

    /**
     * Метод изменения полей пользователя
     * @param Question $question
     * @return Answer
     */
    public function apiUserUpdate($question)
    {
        $answer = new Answer();
        if (!Validator::validate($question, 'userUpdate')) return $answer->genError('Error: failed validation');

        if (!empty($question->userId)) {
            $where = ['id' => $question->userId];
        } elseif (!empty($question->login)) {
            $where = ['login' => $question->login];
        } elseif (!empty($question->email)) {
            $where = ['email' => $question->email];
        } else {
            return $answer->genError('Error: failed to identify user');
        }
        $user = new User($question->getFields());
        $user->setDb($this->db);

        $foundUser = $user->get($where);

        if (!empty($foundUser)) {
            $update = $this->db->update('users', $user->getFields(), $where);
            if (!empty($update) and $update->rowCount() > 0) {
                $answer->success = true;
                $answer->userId = $foundUser['id'];
            } else {
                $answer->genError('Error: failed update');
            }
        } else {
            return $answer->genError('Error: user doesnt exist');
        }

        return $answer;
    }

    /**
     * Метод генерации ключа доступа - токена
     * @param Question $question
     * @return Answer
     */
    public function apiGenToken($question)
    {
//        $user = $this->getCurrentUser();
        $token = new Token($question->getFields());
        $token->setDb($this->db);
        $answer = new Answer();
        $tokenValue = $token->generate();

        if (empty($tokenValue)) {
            $answer->genError('Error: failed token create');
        } else {
            $answer->success = true;
            $answer->token = $tokenValue;
        }

        return $answer;
    }

    /**
     * Метод добавления нового действия
     * @param Question $question
     * @return Answer
     */
    public function apiAddAction(Question $question)
    {
        $answer = new Answer();
        $action = new Action($question->getFields());
        $action->setDb($this->db);

        if (($id = $action->create()) != false) {
            $answer->success = true;
            $answer->actionId = $id;
        } else {
            $answer->genError('Error: failed action create');
        }

        return $answer;
    }

    /**
     * Метод добавления нового закона
     * @param Question $question
     * @return Answer
     */
    public function apiAddLaw($question)
    {
        $answer = new Answer();
        $law = new Law($question->getFields());
        $law->setDb($this->db);

        if (($id = $law->create()) != false) {
            $answer->success = true;
            $answer->lawId = $id;
        } else {
            $answer->genError('Error: failed law create');
        }

        return $answer;
    }

    /**
     * Возвращает строку действия для текущего запроса
     *
     * @param Request $request
     * @return string
     */
    public function getRequestAction($request)
    {
        if (!empty($request->service)) $action = $request->service;
        if (!empty($request->entity)) $action .= '/' . $request->entity;
        if (!empty($request->method)) $action .= '/' . $request->method;
        $action = mb_strtolower(trim($action));

        if(isset($request->params['id']) and is_int($request->params['id'])){
            $altAction = $action.'/'.$request->params['id'];
            if($this->db->has('actions',['name'=>$altAction])){
                $action = $altAction;
            }
        }

        return $action;
    }

    /**
     * Проверяет правомерность выполнения действия для текущего авторизованного пользователя
     *
     * @param string $action
     * @return bool
     */
    public function checkLaw($action)
    {
        $user = $this->getCurrentUser();
        if (!empty($user)) {
            $userId = $user->getId();
        } else {
            $userId = 0;
        }

//        $userId = 1;
        $law = new Law();
        $law->setDb($this->db);
        return $law->access($userId, $action);
    }

    /**
     * Производит авторизацию пользователя по cookies или токену
     *
     * @param array $cookies
     * @param bool|string $token
     * @return bool
     */
    public function auth($cookies = [], $token = false)
    {
        $success = false;
        if (!empty($_COOKIE['au'])) {
            $cookie = $_COOKIE['au'];
        } elseif (!empty($cookies['au'])) {
            $cookie = $cookies['au'];
        }

        if (!empty($cookie)) {
            $user = new User();
            $user->setDb($this->db);
            $user = $user->decryptCookie($cookie);
            if (!empty($user)) {
                $success = true;
                $this->currentUser = $user;
            } else {
                $this->resetCurrentUser();
            }
        }

        if (empty($user) and !empty($token)) {

            $user = new User();
            $user->setDb($this->db);
            $info = $user->getInfoByToken($token);

            if (!empty($info)) {
                $token = new Token(['id' => $info['tokenId'], 'createdAt' => $info['tokenCreatedAt'], 'duration' => $info['tokenDuration']]);
                if ($token->checkDuration()) {
                    $user->pull($info);
                    $success = true;
                    $this->currentUser = $user;
                } else {
                    $this->resetCurrentUser();
                }
            } else {
                $this->resetCurrentUser();
            }
        }

        return $success;

    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiCheckAccess($question){
        $answer = new Answer();

        $request = new Request($question->getFields());
        $this->auth($request->cookies, $request->key);
        $action = $this->getRequestAction($request);

        $answer->access = $this->checkLaw($action);
        if($answer->access){
            $answer->user = $this->getCurrentUser()->getFields();
        }

        return $answer;
    }

    /**
     * Сбрасывает текущего авторизованного пользователя
     */
    public function resetCurrentUser()
    {
        $this->currentUser = null;
    }
}