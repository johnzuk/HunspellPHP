<?php
include '../vendor/autoload.php';

$hunspell = new \HunspellPHP\Hunspell();

var_dump($hunspell->find('to jest test działania alsdkjf sdfsdfsfds'));