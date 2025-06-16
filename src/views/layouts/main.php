<?php
   use app\widgets\Alert;
   use yii\helpers\Html;
   use yii\bootstrap\Nav;
   use yii\bootstrap\NavBar;
   use yii\widgets\Breadcrumbs;
   use app\assets\AppAsset;
   use yii\helpers\Url;
   AppAsset::register($this);
   $this->beginPage();
   ?><!DOCTYPE html>
<html lang="<?= Yii::$app
   ->language ?>">
   <head>
      <meta charset="<?= Yii::$app
         ->charset ?>">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <?php $this->registerCsrfMetaTags(); ?>    
      <title><?= Html::encode(
         ""
         ) ?></title>
      <?php $this->head(); ?>
   </head>
   <body>
      <?php $this->beginBody(); ?>
      <div class="wrap">
         <?php
            NavBar::begin([
                "brandLabel" => "",
                //"brandUrl" => '', //Yii::$app->homeUrl,
                "options" => ["class" => "navbar-inverse navbar-fixed-top"],
            ]);
            $items = [
            ];
            $items = [
                Yii::$app->user->isGuest ? (
                    ['label' => 'Login', 'url' => ['/' . $this->context->module->id . '/default/login']]
                ) : (
                    '<li>'
                    . Html::beginForm(['/' . $this->context->module->id . '/default/logout'], 'post')
                    . Html::submitButton(
                        'Logout (' . Yii::$app->user->identity->username . ')',
                        ['class' => 'btn btn-link logout']
                    )
                    . Html::endForm()
                    . '</li>'
                )
            ];
            echo Nav::widget([
                "options" => ["class" => "navbar-nav navbar-right"],
                "items" => $items,
            ]);
            NavBar::end();
            ?>    
         <div class="container" style="width: 94%; margin-left: 1%; margin-right: 1%;">        
            <?= Breadcrumbs::widget([
               "links" => isset($this->params["breadcrumbs"])
                   ? $this->params["breadcrumbs"]
                   : [],
               ]) ?>        <?= Alert::widget() ?>        <?= $content ?>    
         </div>
      </div>
      <footer class="footer">
         <div class="container">
         </div>
      </footer>
      <?php $this->endBody(); ?>
   </body>
</html>
<?php $this->endPage(); ?>

