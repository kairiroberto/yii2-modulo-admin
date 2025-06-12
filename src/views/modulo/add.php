<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = '➕ Novo Módulo';
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'nome')->textInput(['placeholder' => 'Nome do Módulo'])->label('Nome do Módulo:') ?>

<?= $form->field($model, 'classe')->textInput(['placeholder' => 'Ex: app\\modules\\teste\\Module'])->label('Classe do Módulo:') ?>

<div class="form-group">
    <?= Html::submitButton('Salvar', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
