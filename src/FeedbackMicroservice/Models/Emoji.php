<?php


namespace FeedbackMicroservice\Models;


use Microservices\DataObjects\Model;

class emoji extends Model
{
    public $name;
    public $value;

    public function __construct($fields=[])
    {
        if(!empty($fields)) $this->pull($fields);
    }
    
    
}