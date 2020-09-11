<?php
namespace OrderConveyerMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use OrderConveyerMicroservice\OrderConveyer;
use ContextProviderMicroservice\ContextProvider;
use ContextProviderMicroservice\Models\BA;
use ContextProviderMicroservice\Models\Stage;
use ObjectsMicroservice\Objects;
use AuthenticationMicroservice\Authentication;

class Order extends ArrayObject
{
    protected $id;
    protected $offset;
    protected $limit;
    protected $user;
    
    public $name;
    public $ba;
    public $stage;
    public $obs;
    public $priority;

    


    public function __construct($from=[])
    {
        if(!empty($from)) $this->pull($from);

        $this->limit = $from["limit"];
        $this->offset = $from["offset"];
        
        $this->user = Authentication::getInstance()->getCurrentUser();
        
        
    }
    public function create(){
        
        $q["orders"] = OrderConveyer::getInstance()->db->insert("orders",$this->getFields());

        $us = new User(array(
            "orderId"=> OrderConveyer::getInstance()->db->id()
        ));
        
        if(!$us->create()) return false;

        if($q['orders']->errorInfo()[0]>0){
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
        $us = new User(array(
            "orderId"=>$this->getId()
        ));
        
        if($us->isOwn()){
            $q = OrderConveyer::getInstance()->db->delete("orders",["id[=]"=>$this->getId()]);
            if($q->errorInfo()[0]>0){
                return false;
            }else{
                return true;
            }
            if(!$us->delete()) return false;

            return true;
        }else{
            
            return false;
        }
        
        
    }
    public function edit(){
        $q = OrderConveyer::getInstance()->db->update("orders",$this->getFields(),["id[=]"=>$this->getId()]);

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
    public function addUserOrder()
    {
        $us = new User(array(
            "orderId"=>$this->getId()
        ));
        

        
        if($us->addUserToOrder()){
            return false;
        }else{
            return true;
        } 
    }

    /**
     * Индивидуальное получение заказа по id менеджера и слияние с Бизнес-Алгоритмами.
     * @return bool
     */
    function getOrderById()
    {
        
        $us = new User(array(
            'id'=>$this->getId(),
            "orderId"=>$this->getId()
        ));
        if($us->isOwn()){
            $q = OrderConveyer::getInstance()->db->get("orders","*",[
                "id[=]"=>$this->getId()
            ]);

            $ba = new BA();
            $stages = new Stage();
            $ba->fetchBas();
            //$stages->fetchStages();

            $this->orders = array(
                "order"=>$q,
                "ba"=>$ba->getBas(),
                //"stages"=>$stages->getStages()
            );
            return true;
        }else{
            return false;
        }
        
        
    }
    /**
     * Получение всех заказов менеджера и слияние с Бизнес-Алгоритмами
     */
    function getAllUserOrders()
    {
        $us = new User(array(
            "orderId"=>$this->getId()
        ));

        $ba = new BA();
        $stages = new Stage();
        $ba->fetchBas();
        

        if(!empty($us->getOrders())){
            $res = array();
            $q = OrderConveyer::getInstance()->db->select("orders","*",[
                "id[=]"=>$us->getOrders(),
                "LIMIT"=>[$this->offset,$this->limit]
            ]);
            foreach ($q as $id => $order) {

                
                $res[$id]["order"] = $order;
                foreach ($ba->getBas() as $i => $baItem) {
                    if($baItem["id"]==$order["ba"])
                    {
                        $res[$id]["ba"] =  $baItem;
                        /*$stages->fetchStages([
                            "id[=]"=>json_decode($baItem["stages"],true)
                        ]);
                        $res[$id]["stages"] = $stages->getStages();*/
                        continue;
                    }
                }
                

                

            }
            $this->orders = $res;
            return true;
        }else{
            
            return false;
        }
    }
    public function fetchOrder(){
        
        if($this->getId()!=null) $state = $this->getOrderById();
        else $state = $this->getAllUserOrders();
        return $state;
    }
    public function getOrder(){
        return empty($this->orders)?[]:$this->orders;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    public function getFieldsRelations()
    {
        return[
            'id'=>['id'],
            'name' => ['name'],
            'ba' => ['ba'],
            'stage'=>['stage'],
            'obs'=>['obs'],
            'priority'=>['priority'],
        ];
    }
}
