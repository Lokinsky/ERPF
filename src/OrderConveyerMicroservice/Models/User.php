<?php
namespace OrderConveyerMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use OrderConveyerMicroservice\OrderConveyer;
use AuthenticationMicroservice\Authentication;

class User extends ArrayObject
{
    protected $id;
    protected $offset;
    protected $limit;
    protected $user;
    protected $orders;
    
    public $userId;
    public $orderId;


    


    public function __construct($from=[])
    {
        if(!empty($from)) $this->pull($from);
        
            
        $this->offset = $from["offset"];
        $this->limit = $from["limit"];

        $this->user = Authentication::getInstance()->getCurrentUser();
        $this->orders = OrderConveyer::getInstance()->db->select("users","orderId",[
            "userId[=]"=>$this->user->getId()
        ]);  
        //die(var_dump($this->orders));
        
        
    }
    public function create(){
        
        
        $q["users"] = OrderConveyer::getInstance()->db->insert("users",[
            "userId"=>$this->user->getId(),
            "orderId"=> $this->orderId
        ]);
    

        if($q["orders"]->errorInfo()==null||$q["users"]->errorInfo()==null){
            return false;
        }else{
            return  true;
        }
        
    }
    /**
     * Удаление заказа вместе с удалением записи в таблицы "users"
     * userId=>orderId
     * @return bool
     */
    public function delete(){
        if($this->isOwn()){
            $q = OrderConveyer::getInstance()->db->delete("users",[
                "orderId[=]"=>$this->orderId
            ]);
            return true;
        }
        else{
            return false;
        }

        
        
    }
    public function edit(){
        $q = OrderConveyer::getInstance()->db->update("users",$this->getFields(),["id[=]"=>$this->getId()]);

        if($q->errorInfo()[0]>0){
            return false;
        }else{
            return true;
        }
        
    }

    /**
     * Добавление нового пользователя (самого себя) для управления заказам.
     * @return bool 
     */
    public function addUserToOrder()
    {
        $q = OrderConveyer::getInstance()->db->insert("users",[
            "userId"=>$this->user->getId(),
            "orderId"=>$this->getId()
        ]);

        
        if($q->errorInfo()[0]>0){
            return false;
        }else{
            return true;
        } 
    }

    public function isOwn()
    {
       
        if(in_array($this->getId(),$this->orders)){
            return true;
        }else{
            return false;
        }
    }

    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    public function getFieldsAliases()
    {
        return[
            'id'=>['id'],
            'userId' => ['userId'],
            'orderId' => ['orderId'],
        ];
    }
}
