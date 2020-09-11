<?php
namespace OrderConveyerMicroservice;


use Microservices\Answers\Answer;
use Microservices\Microservice;
use Microservices\Questions\Question;
use OrderConveyerMicroservice\Models\Order;
/**
 * Объект по управлению заказми
 */
class OrderConveyer extends Microservice
{
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiCreateOrder($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'create')) return $answer->genError('Error: validation failed');

        $order = new Order($question->getFields());

        if($order->create()){
            return new Answer($order->getFields());
        }else{
            return $answer->genError("Error: cannot create order in Order");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiEditOrder($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'edit')) return $answer->genError('Error: validation failed');
        $order = new Order($question->getFields());
        if($order->edit()){
            return new Answer($order->getFields());
        }else{
            return $answer->genError("Error: cannot edit order in Order");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiDeleteOrder($question)
    {
        $answer = new Answer();

        if(!Validator::validate($question,'delete')) return $answer->genError('Error: validation failed');
        $order = new Order($question->getFields());
        if($order->delete()){
            return new Answer($order->getFields());
        }else{
            return $answer->genError("Error: cannot delete order");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiGetOrders($question)
    {
        $answer = new Answer();
        if(!Validator::validate($question,'get')) return $answer->genError('Error: validation failed');
        
        
        $order = new Order($question->getFields());

        
        if($order->fetchOrder()){
            return new Answer($order->getOrder());
        }
        else{
            return $answer->genError("Error: cannot get orders in order");
        }
        
    }
    /**
     * @param Question $question
     * @return Answer
     */
    public function apiAddUserOrder($question)
    {
        $answer = new Answer();
  
        if(!Validator::validate($question,'edit')) return $answer->genError('Error: validation failed');
        
        $order = new Order($question->getFields());

        
        if($order->addUserOrder() != false){
            return new Answer($order->getFields());
        }
        else{
            return $answer->genError("Error: cannot add user to order");
        }
        
    }
}