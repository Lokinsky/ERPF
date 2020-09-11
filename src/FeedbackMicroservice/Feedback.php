<?php



namespace FeedbackMicroservice;


use Microservices\Answers\Answer;
use Microservices\Microservice;
use Microservices\Questions\Question;

use AuthenticationMicroservice\Authentication;
use FeedbackMicroservice\Models\Emoji;
use FeedbackMicroservice\Models\Reaction;
use FeedbackMicroservice\Models\Comment;
use FeedbackMicroservice\Models\Rating;
use FeedbackMicroservice\Models\Feedbacks;



class Feedback extends Microservice
{
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiCreateFeedback($question){
        $answer = new Answer();

        $user = Authentication::getInstance()->getCurrentUser();
        
        if(!Validator::validate($question->getFields(),'create')) return $answer->genError('Error: failed validation');
        $data = $question->getFields();
        $data["userId"] = $user->getId();

        $comment = new Comment($data);
        $rating = new Rating($data);

        $comment->setDb($this->db);
        $rating->setDb($this->db);
        
        $data["ratingId"] = $rating->createVote();
        
        $data["commentId"] = $comment->create();
        

        $feedback = new Feedbacks($data);
        $feedback->setDb($this->db);

        $create = $feedback->createFeedback();
        if(empty($create)) return $answer->genError('Error: to create feedback');

        

        $answer->feedback = $create;


        return $answer;
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditFeedback($question){
        $answer = new Answer();

        $user = Authentication::getInstance()->getCurrentUser();
        
        if(!Validator::validate($question->getFields(),'edit')) return $answer->genError('Error: failed validation');
        $data = $question->getFields();
        $data["userId"] = $user->getId();

        $feedback = new Feedbacks($data);
        $feedback->setDb($this->db);

        $get = $feedback->get();

        $data["ratingId"] = $get["ratingId"]; 
        $data["commentId"] = $get["commentId"]; 

        $comment = new Comment($data);
        if(!empty($data["ratingId"])){
            $rating = new Rating($data);
            $rating->setDb($this->db);
            $rating->editVote(); 
        }
        
        $comment->setDb($this->db);
        

        
        $comment->editComment();


        
        $edit = $feedback->editFeedback();

        if(!empty($edit)) return $answer->genError('Error: to edit feedback');

        

        $answer->feedback = !$edit;


        return $answer;
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteFeedback($question){
        $answer = new Answer();

        $user = Authentication::getInstance()->getCurrentUser();
        
        if(!Validator::validate($question->getFields(),'delete')) return $answer->genError('Error: failed validation');

        if($user->getId()==1){
            $feedback = new Feedbacks($question->getFields());

            $feedback->setDb($this->db);
            $get = $feedback->get();
            $comment = new Comment($get);
            $rating = new Rating($get);
    
            $comment->setDb($this->db);
            $rating->setDb($this->db);

            $comment->delete(["id[=]"=>$get["commentId"]]);
            $rating->delete(["id[=]"=>$get["ratingId"]]);
            $delete = $feedback->delete();
                
        }else return $answer->genError('Error: you are not a super user');
        
        
        

        

        if(empty($delete)) return $answer->genError('Error: to delete feedback');

        

        $answer->feedback = $delete;


        return $answer;
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetFeedback($question){
        $answer = new Answer();

        $user = Authentication::getInstance()->getCurrentUser();
        
        //if(!Validator::validate($question->getFields(),'create')) return $answer->genError('Error: failed validation');

        
        $feedback = new Feedbacks($question->getFields());
        $feedback->setDb($this->db);
        $type = $question->getFields()["type"];
        $where = [
            "type"=>$type
        ];
        $findFeedback = $feedback->find($where);
        
        $findRatings = $feedback->find($where,"ratingId");
        $findComments = $feedback->find($where,"commentId");

        $comment    = new Comment($findComments);
        $rating     = new Rating($findRatings);

        $comment->setDb($this->db);
        $rating ->setDb($this->db);

        $comments   = $comment->find();
        $ratings    = $rating->find();
        $feedbacks = array();
        
        
        foreach ($findFeedback as $i => $value) {
            if($value["ratingId"]>0){
                foreach ($ratings as $j => $ra) {
                    if($ra["id"]==$value["ratingId"]){
                        foreach ($comments as $h => $co) {
                            if($co["id"]==$value["commentId"]){
                                array_push($feedbacks,[
                                    "id"=>$value["id"],
                                    "type"=>$value["type"],
                                    "ratingId"=>$value["ratingId"],
                                    "commentId"=>$value["commentId"],
                                    "subjectId"=>$value["subjectId"],
                                    "rating"=>$ra,
                                    "comment"=>$co,
                                ]);
                                continue;
                            }
                            
                        }
                        continue;
                    }
                }
            }
                
            
        }
        if(empty($feedbacks)) return $answer->genError('Error: to get feedback');

        $answer->feedback = $feedbacks;

        return $answer;
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetComment($question){
        $answer = new Answer();

        $user = Authentication::getInstance()->getCurrentUser();
        
        if(!Validator::validate($question->getFields(),'get')) return $answer->genError('Error: failed validation');

        
        $feedback = new Feedbacks($question->getFields());
        $feedback->setDb($this->db);
        $type = $question->getFields()["type"];
        $findFeedback = $feedback->find([
            "type"=>$type
        ]);

        $findComments = $feedback->find([
            "type"=>$type
        ],"commentId");

        $comment    = new Comment($findComments);

        $comment->setDb($this->db);

        $comments   = $comment->find();

        $feedbacks = array();
        
        
        foreach ($findFeedback as $i => $value) {
            if($value["ratingId"]==0){
                foreach ($comments as $h => $co) {
                    if($co["id"]==$value["commentId"]){
                        array_push($feedbacks,[
                            "id"=>$value["id"],
                            "type"=>$value["type"],
                            "commentId"=>$value["commentId"],
                            "subjectId"=>$value["subjectId"],
                            "comment"=>$co,
                        ]);
                        continue;
                    }
                            
                        
                    
                }
            }
        }
        if(empty($feedbacks)) return $answer->genError('Error: to get feedback (commenets)');

        $answer->feedback = $feedbacks;

        return $answer;
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiCreateEmoji($question)
    { 
        $answer = new Answer();

        $user = Authentication::getInstance()->getCurrentUser();
        
        if(!Validator::validate($question->getFields(),'createEmoji')) return $answer->genError('Error: failed validation');

        $emoji = new Emoji($question->getFields());
        $emoji->setDb($this->db);
        $create = $emoji->create();
        if(empty($create)) return $answer->genError('Error: to create Emoji');

        $answer->emoji = $create;

        return $answer;

    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditEmoji($question)
    { 
        $answer = new Answer();

        $user = Authentication::getInstance()->getCurrentUser();
        
        if(!Validator::validate($question->getFields(),'editEmoji')) return $answer->genError('Error: failed validation');

        $emoji = new Emoji($question->getFields());
        $emoji->setDb($this->db);
        $edit = $emoji->update();
        if(empty($edit)) return $answer->genError('Error: to edit Emoji');

        $answer->emoji = $edit;

        return $answer;

    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteEmoji($question)
    { 
        $answer = new Answer();

        $user = Authentication::getInstance()->getCurrentUser();
        
        if(!Validator::validate($question->getFields(),'delete')) return $answer->genError('Error: failed validation');

        $emoji = new Emoji($question->getFields());
        $reaction = new Reaction();
        $emoji->setDb($this->db);
        $reaction->setDb($this->db);
        if($reaction->delete(["emojiId"=>$emoji->getId()]))
            $delete = $emoji->delete();
        
        if(empty($delete)) return $answer->genError('Error: to delete Emoji');

        $answer->emoji = $delete;

        return $answer;

    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiCreateReaction($question)
    { 
        $answer = new Answer();

        $user = Authentication::getInstance()->getCurrentUser();
        
        if(!Validator::validate($question->getFields(),'createReaction')) return $answer->genError('Error: failed validation');
        $fields = $question->getFields();
        $fields["userId"] = $user->getId();
        
        $reaction = new Reaction($fields);
        $reaction->setDb($this->db);
        $create = $reaction->createReaction();
        
        if(empty($create)) return $answer->genError('Error: to Create Reaction');

        $answer->reaction = $create;

        return $answer;

    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditReaction($question)
    { 
        $answer = new Answer();

        $user = Authentication::getInstance()->getCurrentUser();
        
        if(!Validator::validate($question->getFields(),'editReaction')) return $answer->genError('Error: failed validation');
        $fields = $question->getFields();
        $fields["userId"] = $user->getId();
        
        $reaction = new Reaction($fields);
        $reaction->setDb($this->db);
        $edit = $reaction->editReaction();
        
        if(empty($edit)) return $answer->genError('Error: to edit Reaction');

        $answer->reaction = $edit;

        return $answer;

    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteReaction($question)
    { 
        $answer = new Answer();

        $user = Authentication::getInstance()->getCurrentUser();
        
        if(!Validator::validate($question->getFields(),'delete')) return $answer->genError('Error: failed validation');
        $fields = $question->getFields();
        
        $reaction = new Reaction($fields);
        $reaction->setDb($this->db);
        $delete = $reaction->delete([
            "id"=>$reaction->getId(),
            "userId"=>$user->getId()
        ]);
        
        if(empty($delete)) return $answer->genError('Error: to delete Reaction');

        $answer->reaction = $delete;

        return $answer;

    }

}