<?php


namespace CalculationsMicroservice;


use CalculationsMicroservice\Entities\SaleCalculator;
use Microservices\Answers\Answer;
use Microservices\Microservice;
use Microservices\Questions\Question;
use ObjectsMicroservice\SaleObject\Sale;

class Calculations extends Microservice
{

    /**
     * @param Question $question
     * @return Answer
     */
    public function apiCalcSales($question){
        $answer = new Answer();
        $sale = new Sale();
        $sale->setDb($this->db);

        if(isset($question->refresh) and is_bool($question->refresh)){
            $refresh = $question->refresh;
        }else{
            $refresh = true;
        }
        if (!empty($question->ids) and is_array($question->ids)){
            $answer->sales = [];

            foreach ($question->ids as $id){
                if(!Validator::validate(['id'=>$id],'id')) return $answer->genError('Error: validation failed');
                if(!$refresh){
                    $find = $this->findResult($id);
                    if(!empty($find)){
                        $answer->sales[] = $this->normalizeResult($find);
                        continue;
                    }
                }
                $this->pullById($sale,$id);
                $res = $this->calcSale($sale);
                $this->saveResult($sale->getId(),$res);
                $answer->sales[] = $res;
                unset($res);
            }
        }elseif (!empty($question->sales) and is_array($question->sales)){
            $answer->sales = [];
            foreach ($question->sales as $structure){
                if(!Validator::validate($structure,'structure')) return $answer->genError('Error: validation failed');
                $this->pullByStructure($sale,$structure);

                if(!$refresh){

                    $find = $this->findResult($sale->getId());
                    if(!empty($find)){
                        $answer->sales[] = $this->normalizeResult($find);
                        continue;
                    }
                }

                $res = $this->calcSale($sale);
                $this->saveResult($sale->getId(),$res);
                $answer->sales[] = $res;
                unset($res);
            }
        }else{
            $answer->genError('Error: failed to use Sale');
        }


        return $answer;
    }

    public function normalizeResult($result){
        $stories = json_decode($result['story'],true);
        return [
            'cost' => [
                'value' => $result['cost'],
                'compressed' => $stories[0],
            ],
            'price' => [
                'value' => $result['price'],
                'compressed' => $stories[1],
            ],

        ];
    }

    public function findResult($saleId){
        $find = $this->db->get('results','*',['saleId'=>$saleId,'ORDER'=>['id'=>'DESC']]);
        if(!empty($find)) return $find;

        return false;
    }

    public function saveResult($saleId,$result){
        $story = json_encode([$result['cost']['compressed'],$result['price']['compressed']]);
        $data = [
            'saleId' => $saleId,
            'cost' => $result['cost']['value'],
            'price' => $result['price']['value'],
            'story' => $story,
            'createdAt' => time(),
        ];
        if($saleId==0){
            $this->db->update('results',$data,['saleId'=>0]);
        }else
        $this->db->insert('results',$data);
    }

    public function pullById($sale,$id){
        $fields = $sale->get($id);
        $sale->pull($fields);
        $sale->unserialize($sale->getSerialized());
        $sale->refreshComponentsMap();
        $sale->pullComponentsMap();
        $sale->pullFromComponentsMap();
    }

    /**
     * @param Sale $sale
     * @param array $structArray
     */
    public function pullByStructure($sale,$structArray){
        $sale->pullFullFromArray($structArray);
    }



    public function calcSale($sale){
        $calculator = new SaleCalculator();


        return $calculator->calc($sale);
    }
}