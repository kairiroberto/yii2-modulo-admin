<?php

use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

// Transforma o array de módulos em provider
$dataProvider = new ArrayDataProvider([
    'allModels' => array_map(function ($nome, $config) {
        return [
            'nome' => $nome,
            'classe' => $config['class'],
        ];
    }, array_keys($modulos), $modulos),
    'pagination' => false,
]);
?>

<h1>📦 Módulos Registrados</h1>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'nome',
            'format' => 'raw',
            'value' => function ($model) {
                return Html::a("🔍 Ver Estrutura", ['modulo/view', 'id' => $model['nome']]) . " <strong>" . Html::encode($model['nome']) . "</strong>";
            }
        ],
        [
            'attribute' => 'classe',
            'format' => 'text',
        ],
    ],
]); ?>

<p><?= Html::a('➕ Adicionar Novo Módulo', ['modulo/add'], ['class' => 'btn btn-success']) ?></p>
