<?php
/* =================================
  ログ
===================================*/
ini_set('log_errors', 'on');
ini_set('error_log', 'php.log');
ini_set('display_errors', 'on');
error_reporting(E_ALL);

// セッションを開始
session_start();

/*==================================
  デバッグ
===================================*/
$debug_flg = true;
function debug($str){
    global $debug_flg;
    if(!empty($debug_flg)){
        error_log('デバッグ:'.$str);
    }
}
/*===================================
  関数
====================================*/
// スタート
function init(){
    $_SESSION = array();
    History::clear();
    History::set('MISSION START');
    // 倒したキャラクター
    $_SESSION['fleshCount'] = 0;
    // 主人公生成
    createPlayer();
    // 敵キャラ生成
    createMonster();
}
// ゲームオーバー
function gameOver(){
    $_SESSION['gameover'] = true;
}
//主人公生成
function createPlayer(){
    global $players;
    global $leo_flg;
    global $ruka_flg;
    if($leo_flg){
        $_SESSION['player'] = $players[0];
    }elseif($ruka_flg){
        $_SESSION['player'] = $players[1];

    }
}
//敵キャラ生成
function createMonster(){
    global $monsters;
    $_SESSION['monster'] = $monsters[random_int(0,4)];
    $_SESSION['away'] = true; 
    History::set($_SESSION['monster']->getName().'が現れた！');
}
// 敵キャラ撃退
function killMonster(){
    History::set($_SESSION['monster']->getName().'を倒した！');
    //　肉を入手
    $_SESSION['fleshCount'] += $_SESSION['monster']->getFlesh();
    //　新たなモンスター生成
    createMonster();
}
// 距離によるクラス付与
function distanceClass(){
    if($_SESSION['away']==false){
        echo 'approach';
    }elseif($_SESSION['away']==true){
        echo 'away';
    }
}
//　ビームが０の時のクラス付与
function emptyBeamClass(){
    if ($_SESSION['player']->getBeam()<=0) {
        echo 'emptybeam';
    }
}
// ゲームオーバー時の称号
function getRank(){
    $monsterPoint = $_SESSION['fleshCount'];
    if(0 <= $monsterPoint && $monsterPoint <= 99){
        echo 'Eランク';
    }
    if(100 <= $monsterPoint && $monsterPoint <= 199){
        echo 'Dランク';
    }
    if(200 <= $monsterPoint && $monsterPoint <= 299){
        echo 'Cランク';
    }
    if(300 <= $monsterPoint && $monsterPoint <= 399){
        echo 'Bランク';
    }
    if(400 <= $monsterPoint && $monsterPoint <= 499){
        echo 'Aランク';
    }
    if(500 <= $monsterPoint && $monsterPoint <= 599){
        echo 'Sランク';
    }
}