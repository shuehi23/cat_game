<?php
require('function.php');

/*====================================
   変数
======================================*/
// 主人公格納用の空の配列
$players[] = array();
// 敵キャラ格納用の空の配列
$monsters[] = array();
/*====================================
   クラス
======================================*/
//主人公選択クラス
class Character
{
    const Leo = 0;
    const Ruka = 1;
}
// 主人公・モンスター共通の抽象クラス（後でオーバーライド必須）
abstract class Creature
{
    //　プロパティ
    protected $name;
    protected $hp;
    protected $img;
    protected $minAttack;
    protected $maxAttack;
    protected $minRecovery;
    //　コンストラクト
    public function __construct($name, $hp, $img, $minAttack, $maxAttack, $minRecovery)
    {
        $this->name = $name;
        $this->hp = $hp;
        $this->img = $img;
        $this->minAttack = $minAttack;
        $this->maxAttack = $maxAttack;
        $this->minRecovery = $minRecovery;
    }
    // セッターメソッド
    public function setName($str)
    {
        $this->name = $str;
    }
    public function setHp($num)
    {
        $this->hp = $num;
    }
    public function setImg($str)
    {
        $this->img = $str;
    }
    public function setMinRecovery($num)
    {
        $this->minRecovery = $num;
    }
    // ゲッターメソッド
    public function getName()
    {
        return $this->name;
    }
    public function getHp()
    {
        return $this->hp;
    }
    public function getImg()
    {
        return $this->img;
    }
    public function getMinRecovery()
    {
        return $this->minRecovery;
    }
    // 共通の抽象クラス(後でオーバーライド必須)
    abstract public function reaction();
}
// 主人公クラス（後でインスタンス生成）
class Player extends Creature
{
    // プロパティ
    protected $character;
    protected $beam;
    protected $minBeamAttack;
    protected $maxBeamAttack;
    //　コンストラクト
    public function __construct($name, $hp, $img, $character, $beam, $minAttack, $maxAttack, $minBeamAttack, $maxBeamAttack, $minRecovery)
    {
        // 親のコンストラクトを継承
        parent::__construct($name, $hp, $img, $minAttack, $maxAttack, $minRecovery);
        $this->character = $character;
        $this->beam = $beam;
        $this->minBeamAttack = $minBeamAttack;
        $this->maxBeamAttack = $maxBeamAttack;
    }
    // セッターメソッド
    public function setCharacter($num)
    {
        $this->character = $num;
    }
    public function setBeam($num)
    {
        $this->beam = $num;
    }
    // ゲッターメソッド
    public function getCharacter()
    {
        return $this->character;
    }
    public function getBeam()
    {
        return $this->beam;
    }
    //　引っ掻き攻撃メソッド
    public function scratchAttack($targetObj)
    {
        $attackPoint = mt_rand($this->minAttack, $this->maxAttack);
        if (!mt_rand(0, 4)) {
            switch ($this->character) {
                case Character::Leo:
                    History::set('Leoの牙で噛みちぎる！');
                    $attackPoint *= 2.5;
                    break;
                case Character::Ruka:
                    History::set('Rukaの猫パンチ！');
                    $attackPoint *= 2;
                    break;
            }
            $attackPoint = (int)$attackPoint;
        } else {
            History::set('爪で引っ掻いた！');
        }
        $targetObj->setHp($targetObj->getHp() - $attackPoint);
        History::set($attackPoint . 'ダメージ！');
    }
    // ビーム攻撃メソッド
    public function beamAttack($targetObj)
    {
        $attackPoint = mt_rand($this->minBeamAttack, $this->maxBeamAttack);
        History::set($this->getName() . 'はビームを放った！');
        if (!mt_rand(0, 2)) {
            switch ($this->character) {
                case Character::Leo:
                    $attackPoint *= 2.5;
                    break;
                case Character::Ruka:
                    $attackPoint *= 2;
                    break;
            }
            $attackPoint = (int)$attackPoint;
            History::set('急所ヒット！');
        }
        $_SESSION['player']->setBeam($_SESSION['player']->getBeam() - 1);
        $targetObj->setHp($targetObj->getHp() - $attackPoint);
        History::set($attackPoint . 'のダメージ！');
    }
    //　肉入手メソッド
    public function gainFlesh()
    {
        $fleshPoint = mt_rand($_SESSION['monster']->getMinRecovery(), $_SESSION['monster']->getMaxRecovery());
        if (!mt_rand(0, 4)) {
            $fleshPoint *= 1.5;
            $fleshPoint = (int)$fleshPoint;
        }
        $_SESSION['player']->setMinRecovery($_SESSION['player']->getMinRecovery() + $fleshPoint);
        History::set('敵から' . $fleshPoint . 'ポイントの肉を手に入れた！');
    }
    // 肉使用メソッド
    public function useFlesh()
    {
        $recoverPoint = $this->getMinRecovery();
        History::set($this->getName() . 'は肉を食べた');
        $this->setMinRecovery($_SESSION['player']->getMinRecovery() - $recoverPoint);
        $_SESSION['player']->setHp($_SESSION['player']->getHp() + $recoverPoint);
        History::set('HPが' . $recoverPoint . '回復した！');
    }
    //　逃げるメソッド
    public function escape()
    {
        History::set($this->getName() . 'は逃げた！');
        if (!mt_rand(0, 9)) {
            History::set('しかし回り込まれてしまった！');
            $_SESSION['monster']->attack($_SESSION['player']);
            $_SESSION['player']->reaction();
        } else {
            createMonster();
        }
    }
    // リアクションメソッド
    public function reaction()
    {
        switch ($this->character) {
            case Character::Leo;
                History::set('痛いニャー！');
                break;
            case Character::Ruka;
                History::set('まだ子供だニャー！');
                break;
        }
    }
}
// モンスタークラス（後でインスタンス生成）
class Monster extends Creature
{
    //　プロパティ
    protected $minAwayAttack;
    protected $maxAwayAttack;
    protected $voice;
    protected $maxRecovery;
    protected $flesh;
    //　コンストラクタ
    public function __construct($name, $hp, $img, $minAttack, $maxAttack, $minAwayAttack, $maxAwayAttack, $voice, $minRecovery, $maxRecovery, $flesh)
    {
        // 親のコンストラクトを継承
        parent::__construct($name, $hp, $img, $minAttack, $maxAttack, $minRecovery);
        $this->minAwayAttack = $minAwayAttack;
        $this->maxAwayAttack = $maxAwayAttack;
        $this->voice = $voice;
        $this->maxRecovery = $maxRecovery;
        $this->flesh = $flesh;
    }
    // セッターメソッド
    public function setVoice($str)
    {
        $this->voice = $str;
    }
    public function setMaxRecovery($num)
    {
        $this->maxRecovery = $num;
    }
    public function setFlesh($num)
    {
        $this->flesh = $num;
    }
    // ゲッターメソッド
    public function getVoice()
    {
        return $this->voice;
    }
    public function getMaxRecovery()
    {
        return $this->maxRecovery;
    }
    public function getFlesh()
    {
        return $this->flesh;
    }
    // 近距離攻撃メソッド
    public function attack($targetObj)
    {
        $attackPoint = mt_rand($this->minAttack, $this->maxAttack);
        History::set($this->getName() . 'の攻撃！');
        if (!mt_rand(0, 4)) {
            $attackPoint *= 1.5;
            $attackPoint = (int)$attackPoint;
            History::set('致命の一撃');
        }
        $targetObj->setHp($targetObj->getHp() - $attackPoint);
        History::set($attackPoint . 'のダメージ！');
    }
    // 遠距離攻撃メソッド
    public function awayAttack($targetObj)
    {
        $attackPoint = mt_rand($this->minAwayAttack, $this->maxAwayAttack);
        History::set($this->getName() . 'は威嚇した！');
        $targetObj->setHp($targetObj->getHp() - $attackPoint);
        History::set($attackPoint . 'のダメージ！');
    }
    //　リアクションメソッド
    public function reaction()
    {
        History::set($this->voice);
    }
}
// インターフェイス（メッセージ表示）
// 増殖しないのでstatic
interface HistoryInterface
{
    public static function set($str);
    public static function clear();
}
class History implements HistoryInterface
{
    public static function set($str)
    {
        if (empty($_SESSION['history'])) $_SESSION['history'] = '';
        $_SESSION['history'] .= $str . '<br>';
    }
    public static function clear()
    {
        unset($_SESSION['history']);
    }
}
/*========================================
  インスタンス
========================================*/
// 主人公インスタンス
$players[0] = new Player('Leo', 700, 'img/leo.jpeg', Character::Leo, 10, 80, 150, 100, 180, 0);
$players[1] = new Player('Ruka', 650, 'img/ruka.jpeg', Character::Ruka, 10, 60, 130, 150, 240, 0);
// モンスターインスタンス
$monsters[0] = new Monster('百獣の王ライオン', 300, 'img/lion.jpg', 100, 130, 100, 200, 'ガルゥー！', 10, 20, 50);
$monsters[1] = new Monster('氷上の王クマ', 280, 'img/bear.jpg', 90, 130, 90, 170, 'ガオー！', 10, 15, 35);
$monsters[2] = new Monster('川の王ワニ', 250, 'img/crocodile.jpg', 85, 160, 70, 180, 'グルー！', 10, 15, 25);
$monsters[3] = new Monster('密林の王トラ', 270, 'img/tiger.jpg', 95, 150, 60, 190, 'ガルルルル！', 20, 25, 60);
$monsters[4] = new Monster('森の賢者コアラ', 150, 'img/koala.jpg', 70, 110, 60, 140, 'キュー！', 10, 20, 55);

debug('SESSIONの中身(送信前):' . print_r($_SESSION, true));
// post送信されていた場合
if (!empty($_POST)) {
    debug('POST中身:' . print_r($_POST, true));
    debug('SESSSIO中身:' . print_r($_SESSION, true));
    $restart_flg = (!empty($_POST['restart_submit'])) ? true : false;
    $leo_flg = (!empty($_POST['leo_submit'])) ? true : false;
    $ruka_flg = (!empty($_POST['ruka_submit'])) ? true : false;
    History::clear();
    //　スタートボタンが押された場合
    if ($restart_flg) {
        // セッションの中身を空にしてオープニング画面へ
        $_SESSION = array();
    }
    //　主人公Leoを選択
    if ($leo_flg) {
        init();
    }
    // 主人公 Rukaを選択
    if ($ruka_flg) {
        init();
    }
    if (!empty($_POST) && !empty($_SESSION['monster'])) {
        // 近づくボタンが押された場合
        if (!empty($_POST['approach_submit'])) {
            $_SESSION['away'] = false;
        }
        // 離れるボタン
        if (!empty($_POST['away_submit'])) {
            $_SESSION['away'] = true;
        }
        $scratch_flg = (!empty($_POST['scratch_submit'])) ? true : false;
        $beam_flg = (!empty($_POST['beam_submit'])) ? true : false;
        $flesh_flg = (!empty($_POST['flesh_submit'])) ? true : false;
        $escape_flg = (!empty($_POST['escape_submit'])) ? true : false;
        // 近くの場合
        if (empty($_SESSION['away'])) {
            //　引っ掻き攻撃した場合
            if ($scratch_flg) {
                usleep(120000);
                $_SESSION['player']->scratchAttack($_SESSION['monster']);
                $_SESSION['monster']->reaction();
                //　肉を入手した時
                $_SESSION['player']->gainFlesh();
                // モンスターのHPが０になった時
                if ($_SESSION['monster']->getHp() <= 0) {
                    killMonster();
                } else {
                    // モンスターから攻撃を受ける
                    $_SESSION['monster']->attack($_SESSION['player']);
                    $_SESSION['player']->reaction();
                }
            }
            //　ビーム攻撃した時
            if ($beam_flg) {
                if ($_SESSION['player']->getBeam() <= 0) {
                    History::set('弾薬がない');
                } else {
                    usleep(120000);
                    $_SESSION['player']->beamAttack($_SESSION['monster']);
                    $_SESSION['monster']->reaction();
                    // モンスターのHPが０になった時
                    if ($_SESSION['monster']->getHp() <= 0) {
                        killMonster();
                    } else {
                        // モンスターから攻撃を受ける
                        $_SESSION['monster']->attack($_SESSION['player']);
                        $_SESSION['player']->reaction();
                    }
                }
            }
            // 遠くの場合
        } elseif (!empty($_SESSION['away'])) {
            // 引っ掻き攻撃をした場合
            if ($scratch_flg) {
                History::set('この距離では届かない!');
            }
            //　ビーム攻撃をした場合
            if ($beam_flg) {
                // ビーム攻撃が０の場合
                if ($_SESSION['player']->getBeam() <= 0) {
                    History::set('ビーム攻撃は今はできない！');
                } else {
                    usleep(120000);
                    $_SESSION['player']->beamAttack($_SESSION['monster']);
                    $_SESSION['monster']->reaction();
                    // モンスターのHPが０になった場合
                    if ($_SESSION['monster']->getHp() <= 0) {
                        killMonster();
                    } else {
                        // モンスターから攻撃を受ける
                        $_SESSION['monster']->attack($_SESSION['player']);
                        $_SESSION['player']->reaction();
                    }
                }
            }
        }
        // 肉ボタンが押された時
        if ($flesh_flg) {
            $_SESSION['player']->useFlesh();
        }
        //　逃げるボタンを押した場合
        if ($escape_flg) {
            $_SESSION['player']->escape();
        }
        // 主人公のHPが０になった時
        if ($_SESSION['player']->getHp() <= 0) {
            gameOver();
        }
    }
    // post送信されていない場合
} elseif (empty($_POST)) {
    $_SESSION = array();
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>game</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="main">
        <?php if (empty($_SESSION['player']) && empty($_SESSION['gameover'])) : ?>
            <div class="opening-container">
                <h1>Get meat challenge</h1>
                <h2>MISSION：現れた敵を倒し、大好物な肉を手に入れろ！！</h2>
                <div class="tips">
                    <p>TIPS</p>
                    <p>ルール１：目からビームは威力はあるが、使用回数に限りがある。</p>
                    <p>ルール２：爪で攻撃し、敵から黒毛和牛を入手できる。食べると回復する。</p>
                    <p>ルール３：離れると被ダメージは減るが、爪で攻撃はできない。</p>
                </div>
                <p>▽猫を選択してください</p>
                <form action="" method="post">
                    <div class="select-player-area">
                        <button type="submit" name="leo_submit" value="leo_submit">
                            <div class="player-container" style="background-color:#fcff41;">
                                <div class="player-inner">
                                    <div class="player-img-area">
                                        <img src="img/leo.jpeg" alt="">
                                        <p>Leo</p>
                                    </div>
                                    <div class="player-status-container">
                                        <p>HP:700</p>
                                        <p>ひっかく：50ー80</p>
                                        <p>ビーム：100ー180</p>
                                    </div>
                                </div>
                            </div>
                        </button>
                        <button type="submit" name="ruka_submit" value="ruka_submit" style="background-color:#23afaf;">
                            <div class="player-container">
                                <div class="player-inner">
                                    <div class="player-img-area">
                                        <img src="img/ruka.jpeg" alt="">
                                        <p>Ruka</p>
                                    </div>
                                    <div class="player-status-container">
                                        <p>HP：600</p>
                                        <p>ひっかく：60ー130</p>
                                        <p>ビーム：150ー240</p>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        <?php elseif (!empty($_SESSION) && empty($_SESSION['gameover'])) : ?>
            <div class="battle-container">
                <div class="title-area">
                    <h1>Get meat challenge</h1>
                    <form action="" method="post">
                        <button type="submit" name="restart_submit" value="restart_submit">▷リスタート</button>
                    </form>
                </div>
                <div class="battle-area <?php
                                        if ($_SESSION['away'] == false) {
                                            echo 'approach';
                                        } elseif ($_SESSION['away'] == true) {
                                            echo 'away';
                                        } ?>">
                    <div class="enemy-area">
                        <div class="enemy-status-area">
                            <p><?php echo $_SESSION['monster']->getName(); ?></p>
                            <p>HP：<?php echo $_SESSION['monster']->getHp(); ?></p>
                        </div>
                        <div class="enemy-img-area">
                            <img src="<?php echo $_SESSION['monster']->getImg(); ?>" alt="" class="monster-img<?php distanceClass(); ?>">
                            <img src="img/scratch02.png" alt="" width="200" class="js-scratch-wound">
                            <img src="img/beam-cutout.png" alt="" width="200" class="js-beam-wound">
                        </div>
                    </div>
                </div>
                <div class="player-area">
                    <div id="js-scroll-bottom" class="msg-wrapper">
                        <?php if (!empty($_SESSION['history'])) echo $_SESSION['history']; ?>
                    </div>
                    <div class="player-wrapper">
                        <div class="player-information-wrapper">
                            <div class="player-information-inner">
                                <div class="player-img-area">
                                    <img src="<?php echo $_SESSION['player']->getImg(); ?>" alt="">
                                </div>
                                <div class="player-status-container">
                                    <p><?php echo $_SESSION['player']->getName(); ?></p>
                                    <p>HP：<?php echo $_SESSION['player']->getHp(); ?></p>
                                    <p>肉：<?php echo $_SESSION['fleshCount']; ?></p>
                                </div>
                            </div>
                        </div>
                        <form action="" method="post">
                            <div class="command-wrapper">
                                <div class="command escape-command">
                                    <button type="submit" name="escape_submit" value="escape_submit">▷逃げる</button>
                                    <button type="submit" name="flesh_submit" value="flesh_submit">▷回復<p>(<?php echo $_SESSION['player']->getMinRecovery(); ?>)</p></button>
                                </div>
                                <div class="command distance-command">
                                    <button type="submit" class="js-approach" name="approach_submit" value="approach_submit">▷近づく</button>
                                    <button type="submit" class="js-away" name="away_submit" value="away_submit">▷離れる</button>
                                </div>
                                <div class="command attack-command">
                                    <button type="submit" name="scratch_submit" value="scratch_submit" class="js-scratch-btn <?php distanceClass(); ?>">▷引っ掻く</button>
                                    <button type="submit" name="beam_submit" value="beam_submit" class="js-beam-btn" <?php emptyBeamClass(); ?>>▷ビーム<p>(<?php echo $_SESSION['player']->getBeam(); ?>)</p></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php elseif (!empty($_SESSION['gameover'])) : ?>
            <div class="gameover-container <?php echo $_SESSION['player']->getName(); ?>">
                <div class="gameover-inner">
                    <h1>YOU ARE DEAD</h1>
                    <p>獲得した肉：<?php echo $_SESSION['fleshCount']; ?></p>
                    <p>称号：<?php echo getRank(); ?></p>
                    <form action="" method="post">
                        <button type="submit" name="restart_submit" value="restart_submit">▷continue</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer id="footer">
        <div class="container">
            <div class="text-center">
                Copyright 2020
                <a href="" class="">Get meat challenge</a>
            </div>
        </div>
    </footer>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        $(function() {
            // メッセージを下部に固定
            $('#js-scroll-bottom').animate({
                scrollTop: $('#js-scroll-bottom')[0].scrollHeight
            }, 'fast');
        });
    </script>

</body>

</html>
