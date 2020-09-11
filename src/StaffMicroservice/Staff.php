<?php


namespace StaffMicroservice;


use AuthenticationMicroservice\Authentication;
use Microservices\Answers\Answer;
use Microservices\Microservice;
use Microservices\Questions\Question;
use StaffMicroservice\Entities\Role;
use StaffMicroservice\Entities\Worker;

class Staff extends Microservice implements StaffMicroserviceI
{
    protected $currentWorker;

    public function getCurrentWorker(){
        if(empty($this->currentWorker)){
            $userId = Authentication::getInstance()->getCurrentUser()->getId();
            $worker = new Worker();
            $workerInfo = $worker->getByUserId($userId);
            if(empty($workerInfo)) {
                $workerInfo = ['id'=>0];
            }

            $worker->pull($workerInfo);

            $this->currentWorker = $worker;
        }


        return $this->currentWorker;
    }

    /**
     * Создаёт роль
     * @param Question $question
     * @return Answer
     */
    public function apiAddRole($question)
    {
        $answer = new Answer();
        $role = new Role($question->getFields());

        if (($answer->roleId = $role->create()) != false) {
            $answer->success = true;
        } else {
            $answer->genError('Error: failed role create');
        }

        return $answer;
    }

    /**
     * Изменяет существующую роль
     * @param Question $question
     * @return Answer
     */
    public function apiEditRole($question)
    {
        $answer = new Answer();

        if (!Validator::validate($question, 'roleEdit')) return $answer->genError('Error: failed validation');
        $role = new Role($question->getFields());
        unset($role->createdAt);

        $foundRole = Staff::getInstance()->db->get('roles', '*', ['id' => $role->getId()]);
        if (!empty($foundRole)) {
            $edit = Staff::getInstance()->db->update('roles', $role->getFields(), ['id' => $role->getId()]);

            if (!empty($edit) and $edit->rowCount() > 0) {
                $answer->success = true;
                $answer->userId = $foundRole['id'];
            } else {
                $answer->genError('Error: failed update');
            }
        } else {
            return $answer->genError('Error: role not found');
        }

        return $answer;
    }


    /**
     * Получает данные роли по имени или id
     * @param Question $question
     * @return Answer
     */
    public function apiGetRole($question)
    {
        $answer = new Answer();
        if (!Validator::validate($question, 'roleGet')) return $answer->genError('Error: validation failed');

        if (!empty($question->id)) {
            $where = ['id' => $question->id];
        } elseif (!empty($question->name)) {
            $where = ['name' => $question->name];
        } else {
            return $answer->genError('Error: failed to identify role');
        }

        $foundRole = Staff::getInstance()->db->get('roles', '*', $where);

        if (!empty($foundRole)) {
            $answer->role = $foundRole;
        } else {
            return $answer->genError('Error: role not found');
        }

        return $answer;
    }


    /**
     * Удаляет роль по имени или id
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteRole($question)
    {
        $answer = new Answer();
        if (!Validator::validate($question, 'roleGet')) return $answer->genError('Error: failed validation');

        if (!empty($question->id)) {
            $where = ['id' => $question->id];
        } elseif (!empty($question->name)) {
            $where = ['name' => $question->name];
        } else {
            return $answer->genError('Error: failed to identify role');
        }

        $delete = Staff::getInstance()->db->delete('roles', $where);

        if (!empty($delete) and $delete->rowCount() > 0) {
            $answer->success = true;
        } else {
            return $answer->genError('Error: failed delete role');
        }

        return $answer;
    }

    /**
     * Создаёт запись о работнике
     * @param Question $question
     * @return Answer
     */
    public function apiAddWorker($question)
    {
        $answer = new Answer();
        if (isset($question->phones) and is_array($question->phones)) $question->phones = json_encode($question->phones);
        if (isset($question->links) and is_array($question->links)) $question->links = json_encode($question->links);
        if (isset($question->skills) and is_array($question->skills)) $question->skills = json_encode($question->skills);
        if (isset($question->attachments) and is_array($question->attachments)) $question->attachments = json_encode($question->attachments);

        $worker = new Worker($question->getFields());

        if (($id = $worker->create()) != false) {
            $answer->success = true;
            $answer->workerId = $id;
        } else {
            $answer->genError('Error: failed worker create');
        }

        return $answer;
    }

    /**
     * Изменяет запись работника
     * @param Question $question
     * @return Answer
     */
    public function apiEditWorker($question)
    {
        $answer = new Answer();

        if (!Validator::validate($question, 'workerUpdate')) return $answer->genError('Error: failed validation');

        if (!empty($question->id)) {
            $where = ['id' => $question->id];
        } elseif (!empty($question->userId)) {
            $where = ['userId' => $question->userId];
        } else {
            return $answer->genError('Error: failed to identify worker');
        }
        $worker = new Worker($question->getFields());

        $foundWorker = $this->db->get('workers', '*', $where);

        if (!empty($foundWorker)) {
            $update = $this->db->update('workers', $worker->getFields(), $where);
            if (!empty($update) and $update->rowCount() > 0) {
                $answer->success = true;
                $answer->workerId = $foundWorker['id'];
            } else {
                $answer->genError('Error: failed update');
            }
        } else {
            return $answer->genError('Error: worker doesnt exist');
        }

        return $answer;
    }


    /**
     * Получить данные о работнике по id или id пользователя
     * @param Question $question
     * @return Answer
     */
    public function apiGetWorker($question)
    {
        $answer = new Answer();
        if (!Validator::validate($question, 'workerGet')) return $answer->genError('Error: validation failed');

        if (!empty($question->id)) {
            $where = ['id' => $question->id];
        } elseif (!empty($question->userId)) {
            $where = ['userId' => $question->userId];
        } else {
            return $answer->genError('Error: failed to identify worker');
        }

        $foundWorker = $this->db->get('workers', '*', $where);

        if (!empty($foundWorker)) {
            $answer->worker = $foundWorker;
        } else {
            return $answer->genError('Error: worker not found');
        }

        return $answer;
    }


    /**
     * Добавить роль работнику
     * @param Question $question
     * @return Answer
     */
    public function apiAddRoleToWorker($question)
    {
        $answer = new Answer();

        if (!Validator::validate($question, 'addRoleToWorker')) return $answer->genError('Error: failed validation');

        if (!empty($this->db->insert('casting', ['roleId' => $question->roleId, 'workerId' => $question->workerId, 'createdAt' => time()]))) {
            $answer->success = true;
            $answer->castingId = $this->db->id();
        } else {
            return $answer->genError('Error: failed add role to worker');
        }

        return $answer;
    }

    /**
     * Получить роли работника
     * @param Question $question
     * @return Answer
     */
    public function apiGetWorkerRoles($question)
    {
        $answer = new Answer();
        $worker = new Worker($question->getFields());
        if (!Validator::validate($worker, 'getWorkerRoles')) return $answer->genError('Error: failed validation');

        if (!empty($question->userId) and empty($question->id)) {
            $where = ['userId' => $question->userId];
            $workerD = $this->db->get('workers', '*', $where);
            if (empty($workerD)) return $answer->genError('Error: worker not found');
            $workerId = $workerD['id'];
        } else {
            $workerId = $worker->getId();
        }

        $roles = $this->db->select('casting', ["[>]roles" => ["roleId" => "id"]], '*', ['workerId' => $workerId]);

        if (!empty($roles)) {
            $answer->roles = $roles;
        } else {
            return $answer->genError('Error: roles not found');
        }


        return $answer;
    }

    /**
     * Удалить роль у работника
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteRoleForWorker($question)
    {
        $answer = new Answer();

        if (!Validator::validate($question, 'addRoleToWorker')) return $answer->genError('Error: validation failed');

        $delete = $this->db->delete('casting', [
            'AND' => [
                'roleId' => $question->roleId,
                'workerId' => $question->workerId,
            ]
        ]);

        if (!empty($delete) and $delete->rowCount() > 0) {
            $answer->success = true;
        } else {
            $answer->genError('Error: failed role detach');
        }

        return $answer;
    }

    /**
     * Получить данные о работниках
     * @param Question $question
     * @return Answer
     */
    public function apiGetWorkers($question)
    {
        $answer = new Answer();

        if (isset($question->offset) and Validator::ruleInt($question->offset)) {
            $offset = $question->offset;
        } else {
            $offset = 0;
        }

        if (isset($question->count) and Validator::ruleInt($question->count)) {
            $count = $question->count;
        } else {
            $count = 50;
        }

        $workers = $this->db->select('workers', '*', [
            'LIMIT' => [$offset, $count]
        ]);

        $answer->workers = $workers;

        return $answer;
    }


}