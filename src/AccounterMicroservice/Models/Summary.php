<?php
namespace AccounterMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use AccounterMicroservice\Accounter;
/**
 * Модель таблицы общей сводки.
 */
class Summary extends ArrayObject
{
    protected $id;
    protected $limit;
    protected $offset;


    protected $summaries;
    
    public $name;
    public $description;
    public $ownerId;
    public $amount;
    public $type;
    public $date;


    public function __construct($from=[])
    {
        if(!empty($from)) $this->pull($from);
        
        $this->limit = $from["limit"];
        $this->offset = $from["offset"];
        
        
    }
    public function create(){
        $q = Accounter::getInstance()->db->insert("summary",$this->getFields());
        if($q->errorInfo()[0]>0){
            return false;
        }else{
            return  true;
        }
        
    }
    public function delete(){
        $q = Accounter::getInstance()->db->delete("summary",["id[=]"=>$this->getId()]);
        if($q->errorInfo()[0]>0){
            return false;
        }else{
            return  true;
        }
        
    }
    public function edit(){
        $q = Accounter::getInstance()->db->update("summary",$this->getFields(),["id[=]"=>$this->getId()]);
        if($q->errorInfo()[0]>0){
            return false;
        }else{
            return  true;
        }
        
    }
    public function fetchSummaries(){
        $q = Accounter::getInstance()->db->select("summary","*",[
            "LIMIT" => [$this->offset,$this->limit]
        ]);
        
        $this->summaries = $q;
        
        if($q==0){
            return false;
        }else{
            return  true;
        }
        
    }
    public function getSummaries(){
        return $this->summaries;
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
            'name' => ['name'],
            'description' => ['description','descr'],
            'ownerId'=>['ownerId'],
            'amount'=>['amount'],
            'type'=>['type'],
            'date'=>['date'],
        ];
    }
}
