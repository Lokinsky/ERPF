<?php


namespace FeedbackMicroservice\Models;


use Microservices\DataObjects\Model;

class Feedbacks extends Model
{

    /*protected $value;

    protected $userId;
    protected $objectId;
    protected $body;
    */

    public $ratingId;
    public $subjectId;
    public $commentId;
    public $type;
    public $createdAt;

    public function __construct($fields=[])
    {
        if(!empty($fields)) $this->pull($fields);
        
    }
    public function createFeedback()
    {
        if(!empty($this->ratingId))
            $isCreated = $this->exists([
                "AND"=>[
                    "ratingId"=>$this->ratingId,
                    "subjectId"=>$this->subjectId,
                    "type"=>$this->type
                ]
            ]);
        
        
        if(!$isCreated or empty($this->ratingId)){
            $create = $this->create();
            return $create;
        }
        else{
            
            return false;
        }
    }

    public function editFeedback()
    {
        $createdTime = $this->get([
            "AND"=>[
                "id"=>$this->getId(),
                "type"=>$this->type
            ]
            ],"createdAt");
        $this->createdAt = $createdTime;
           
        if((time()-$createdTime)<=86400){
            $update = $this->update();
            
            return $update;
        }
        else return false;
    }
    
    public function getType()
    {
        return 3;
    }

}