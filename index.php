<?php

use ERPF\ERP;

require __DIR__."/vendor/autoload.php";

$sale = new \ObjectsMicroservice\SaleObject\Sale();
$sale->price = 10;
$sale->name = 'sale';
$sale->cost = 10;
$mf4 = $sale->addNewComponent('127');
$mf4->name = 'mf4';
$mf4->type = 33221;
$mf4->value = 50;

$service = $sale->addNewComponent('11');
$service->name = 'service';
$service->price = 1;
$service->cost = 1;
$prop1 = $service->addNewComponent('56');
$prop1->name = 'prop1';
$prop1->price = 5;
$prop1->cost = 5;

$modifier3 = $service->addNewComponent('117');
$modifier3->name = 'mf3';
$modifier3->value = 0.111;
$modifier3->type = 43130;

$product = $service->addNewComponent('33');
$product->name = 'product';
$product->price = 3;
$product->cost = 3;
$prop2 = $product->addNewComponent('66');
$prop2->name = 'prop2';
$prop2->price = 6;
$prop2->cost = 6;

$operation = $sale->addNewComponent('22');
$operation->name = 'operation';
$operation->price = 2;
$operation->cost = 2;

$modifier1 = $operation->addNewComponent('97');
$modifier1->name = 'mf1';
$modifier1->value = 100;
$modifier1->type = 11130;

$item = $operation->addNewComponent('44');
$item->name = 'item';
$item->price = 4;
$item->cost = 4;
$prop3 = $item->addNewComponent('76');
$prop3->name = 'prop3';
$prop3->price = 7;
$prop3->cost = 7;

$material = $operation->addNewComponent('85');
$material->name = 'material';
$material->price = 8;
$material->cost = 8;

$modifier2 = $material->addNewComponent('107');
$modifier2->name = 'mf2';
$modifier2->value = 5;
$modifier2->type = 21131;

$saleArr = $sale->getFullAsArray();


$superuserToken = 'a6bb521e02ec5def6b6f3bab4e88194d618217efe086361e76fdb91f67d8adfc';

$arr = [
    'v' => 1.7,
    'reqs' => [
        [
            's' => 'Feedback',
            'meth' => 'GetComment',
            'k' => "a6bb521e02ec5def6b6f3bab4e88194d618217efe086361e76fdb91f67d8adfc",
            'params' => [
                'type'=>4,
                
                
            ],
            
        ]
    ],

];


$_POST = $arr;

//$encKey = Key::loadFromAsciiSafeString($keyAscii);

//var_dump($encKey);

//$url = 'https://sun9-33.userapi.com/2fabDxjf7LnsRhZQSay0cRaCxPKAG8XuQj-YwQ/nv96qCW4BCs.jpg';
//$files = new \FilesMicroservices\Files();
//var_dump($files->downloadFileByUrl($url));

$erp = ERP::getInstance();
$erp->run();
