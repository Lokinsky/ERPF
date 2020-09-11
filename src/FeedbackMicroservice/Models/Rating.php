<?php


namespace FeedbackMicroservice\Models;


use Microservices\DataObjects\Model;

class Rating extends Model
{
    protected $ratingId;
    public $subjectId;
    public $userId;
    public $value;
    public $createdAt;

    public function __construct($fields=[])
    {
        if(!empty($fields)) $this->pull($fields);
    }
    public function createVote()
    {
        $isVoted = $this->exists([
            "AND"=>[
                "id"=>empty($this->ratingId)?$this->getId():$this->ratingId,
                "userId"=>$this->userId
            ]
        ]);
        
        if(!$isVoted){
            $create = $this->create();
            
            return $create;
        }
        else{
            return false;
        }
    }

    public function editVote()
    {
        $createdTime = $this->get([
            "AND"=>[
                "id"=>empty($this->ratingId)?$this->getId():$this->ratingId,
                "userId"=>$this->userId
            ]
            ],"createdAt");

        if((time()-$createdTime)<=86400){
            
            return $this->update([
                "value"=>$this->value,
            ],[
                "AND"=>[
                    "id"=>empty($this->ratingId)?$this->getId():$this->ratingId,
                    "userId"=>$this->userId
                ]
            ]);
        }
    }    

}