<?php
use \yii\helpers\Html;
/* @var $this yii\web\View */
/* @var array $itemPrizes  */
/* @var string $moneyLimit */

?>
<div class="site-index">
    <div class="row">
        <div class="col-sm-4">
            <h3>Ваши предметы:</h3>
            <ul>
            <?php foreach ($itemPrizes as $item) { ?>
                <li><?= $item->value . ' (' . $item->value . '$' . ')' ?>
                    <?= Html::a('(обменять на деньги)',
                        ['exchange'], ['id' => $item->id, 'class' => 'item'])
                    ?>
                </li>
            <?php } ?>
            </ul>
        </div>
        <div class="col-sm-8 jumbotron">
            <h2 id="message">Розыгрыш!</h2>

            <p class="lead">Нажмите кнопку, чтобы получить ваш приз!</p>

            <p>
                <?= Html::button('Разыграть приз',
                    [
                        'class' => 'btn btn-lg btn-success',
                        'id' => 'play',
                        'url' => Yii::$app->urlManager->createUrl('site/play')
                    ]); ?>
            </p>
        </div>
    </div>


    <div class="body-content">

        <div class="row">
            <div class="col-lg-4">
                <?php if ($itemPrizes) { ?>
                    <h3>Разыгрываются товары:</h3>
                    <ul>
                        <?php foreach ($itemPrizes as $item) { ?>
                        <li><?= $item->title ?> (<?= $item->price ?> $)</li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <h3>Все товары разыграны</h3>
                <?php } ?>
            </div>
            <div class="col-lg-4">
                <?php if ($moneyLimit) { ?>
                <h3>Денежные призы (призовой фонд: <?= $moneyLimit ?>)</h3>
                Можно выйграть от 1 до <?= $moneyLimit ?> $
                <?php } else { ?>
                    Ренежные призы разыграны
                <?php } ?>
            </div>
            <div class="col-lg-4">
                <h3>Баллы лояльности</h3>
                Можно выиграть от 1 до <?= Yii::$app->params['rules']['maxPoints'] ?>
            </div>
        </div>

    </div>
</div>
