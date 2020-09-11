<?php


namespace FeedbackMicroservice;


use Validators\BasicValidator;

class Validator extends BasicValidator
{


    public static function getRules()
    {
        return [
            
            "create"=>[
                'id' => ['empty'],
                'subjectId' => [['lmax' => 11]],
                'objectId' => [['lmax' => 11]],
                'body' => [['length' => [1, 255]]],
                'value' => [['lmax' => 11],['rmax'=>100]],
            ],
            "createEmoji"=>[
                'id' => ['empty'],
                'name' => [['length' => [1, 32]]],
                'value' => [['lmax' => 11],['rmax'=>100]],
            ],
            "createReaction"=>[
                'id' => ['empty'],
                'emojiId ' => [['lmax' => 11]],
                'objectId' => [['lmax' => 11]],
                'type' => [['lmax' => 1]],
            ],
            "delete"=>[
                'id'=>['!empty']
            ],
            'edit' => [
                'id' => ['!empty'],
                'body' => [['length' => [1, 255]]],
                'value' => [['lmax' => 11],['rmax'=>100]],
            ],
            'editEmoji' => [
                'id' => ['!empty'],
                'name' => [['length' => [1, 32]]],
                'value' => [['lmax' => 11],['rmax'=>100]],
            ],
            'editReaction ' => [
                'id' => ['!empty'],
                'emojiId ' => [['lmax' => 11]],
                'objectId' => [['lmax' => 11]],
                'type' => [['lmax' => 1]],
            ],
            'get'=>[
                'id'=> [['lmax'=>11]],
                'type'=> [['lmax'=>11]]
            ]
        ];
    }
}