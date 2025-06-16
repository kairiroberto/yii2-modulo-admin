<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = '➕ Novo Módulo';
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'nome')
    ->textInput(['id' => 'input-nome', 'placeholder' => 'Nome do Módulo'])
    ->label('Nome do Módulo:') ?>

<?= $form->field($model, 'classe')
    ->textInput(['id' => 'input-classe', 'placeholder' => 'Ex: app\\modules\\teste\\Module'])
    ->label('Classe do Módulo: (app\\modules\\NOME\\Module)') ?>

<div class="form-group">
    <?= Html::submitButton('Salvar', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
document.getElementById('input-nome').addEventListener('input', function() {
    const nome = this.value.trim().toLowerCase();
    if(nome) {
        const classe = 'app\\\\modules\\\\' + nome + '\\\\Module';
        document.getElementById('input-classe').value = classe;
    } else {
        document.getElementById('input-classe').value = '';
    }
});
JS;
$this->registerJs($js);
?>
