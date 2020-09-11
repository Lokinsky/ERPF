<?php


namespace FeedbackMicroservice\Models;


use Microservices\DataObjects\Model;

class Comment extends Model
{
    protected $commentId;
    public $userId;
    public $objectId;
    public $body;
    public $createdAt;

    public function __construct($fields=[])
    {
        if(!empty($fields)) $this->pull($fields);
    }
    
    public function editComment()
    {
        $createdTime = $this->get([
            "AND"=>[
                "userId "=>$this->userId,
                "id"=>!empty($this->commentId)?$this->commentId:$this->getId()
            ]
            ],"createdAt");
        if((time()-$createdTime)<=86400){
          
            return $this->update([
              "body"=>$this->body,
          ],[
              "AND"=>[
                "userId "=>$this->userId,
                "id"=>$this->commentId
            ]
            ]);
        }
    }
    public function getType()
    {
        return 2;
    }
    

}