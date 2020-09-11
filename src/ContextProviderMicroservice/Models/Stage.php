<?php
namespace ContextProviderMicroservice\Models;

use Microservices\DataObjects\ArrayObject;
use ContextProviderMicroservice\ContextProvider;


class Stage extends ArrayObject
{
    protected $id;
    protected $limit;
    protected $offset;

    protected $summaries;

    public $name;
    public $description;
    protected $stages;

    public function __construct($from=[])
    {
        if(!empty($from)) $this->pull($from);
        
        $this->limit = $from["limit"];
        $this->offset = $from["offset"];
         
        
    }

    public function create(){
        
        $q = ContextProvider::getInstance()->db->insert("stages",$this->getFields());
    
        if($q->errorInfo()[0]>0)
        return  false;
        else return true;
    }

    public function edit(){
        $q = ContextProvider::getInstance()->db->update("stages",$this->getFields(),[
            "id"=>$this->getId()
        ]);
        if($q->errorInfo()[0]>0)
        return  false;
        else return true;
    }
    public function delete(){
        if($this->updateStagesFields())
            $q = ContextProvider::getInstance()->db->delete("stages",[
                'AND'=>
                [
                    "id[=]"=>$this->getId()
                ]
            ]);
        $this->description = "deleted on id = ".$this->id;
        if($q->errorInfo()[0]>0)
        return  false;
        else return true;
    }

   public function fetchStages($where=[]){
        if(empty($where))
            $where = [
                "LIMIT" => [$this->offset,$this->limit]
            ];
        $q = ContextProvider::getInstance()->db->select("stages","*",$where);

    
    if($q>0){
        $this->stages = $q;
        return true;
    } 
    else return false;
        
   }
    public function getStages(){
        return $this->stages;
    }
    /**
     * Аналог каскадного обновления
     * При удалении "stage" ─ этапа, вызывается данная функция для обновления списков этапов для ба и контекстов.
     * @return bool 
     */
    function updateStagesFields()
    {
        
        $rel = [
            "contexts"=>ContextProvider::getInstance()->db->select("contexts","*","."),
            "bas" => ContextProvider::getInstance()->db->select("ba","*",".")
        ];
        foreach ($rel as $name => $models) {
            foreach ($models as $nm => $model) {
                foreach (json_decode($model["stages"],true) as $i => $stage) {
                    
                    if((int)$stage==$this->getId()){
                        $r = json_decode($model["stages"],true);
                        $res = array();
                        foreach ($r as $key => $value) {
                            if($this->getId()!=$value){
                                array_push($res,$value);
                            }
                        }
                        $model["stages"] = $res;
                        if($name=="contexts"){
                            if(empty($model["stages"])){
                                $model["stages"] = "";
                            }
                            $context = new Context([
                                "id"=>$model["id"],
                                "name"=>$model["name"],
                                "stages"=>$model["stages"],
                                "description"=>$model["description"]
                            ]);
                            $context->edit();
                            

                        }else{
                            if(empty($model["stages"])){
                                $model["stages"] = "";
                            }
                            $ba = new BA([
                                "id"=>$model["id"],
                                "name"=>$model["name"],
                                "stages"=>$model["stages"],
                                "description"=>$model["description"]
                            ]);
                            $ba->edit();
                            
                        }
                        
                    }
                }            
            }
        }
        return true;


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
            'description' => ['description'],
        ];
    }
}
