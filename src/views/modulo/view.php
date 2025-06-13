<?php
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
?>
<h1>📁 Estrutura do Módulo: <code><?= $id ?></code></h1>

<ul>
    <li><strong>Module.php:</strong> <?= $estrutura['Module.php'] ? '✅ Existe' : '❌ Não encontrado' ?></li>
</ul>

<h3>📂 Controllers</h3>
<ul>
    <?php foreach ($estrutura['controllers'] as $file): if ($file === '.' || $file === '..') continue; ?>
        <li><?= $file ?></li>
    <?php endforeach; ?>
</ul>

<h3>📂 Models</h3>
<ul>
    <?php foreach ($estrutura['models'] as $file): if ($file === '.' || $file === '..') continue; ?>
        <li><?= $file ?></li>
    <?php endforeach; ?>
</ul>

<h3>📂 Views</h3>
<ul>
    <?php foreach ($estrutura['views'] as $file): if ($file === '.' || $file === '..') continue; ?>
        <li><?= $file ?></li>
    <?php endforeach; ?>
</ul>

<h3>📂 Views/Layouts</h3>
<ul>
    <?php foreach ($estrutura['views/layouts'] as $file): if ($file === '.' || $file === '..') continue; ?>
        <li><?= $file ?></li>
    <?php endforeach; ?>
</ul>

<h3>📂 Links</h3>
<ul>
    <?php foreach ($estrutura['controllers'] as $file): if ($file === '.' || $file === '..') continue; ?>
        <li>
            <?= $file ?>
            <?php
                if (str_ends_with($file, 'Controller.php')) {
                    $controllerId = str_replace('Controller.php', '', $file);
                    $controllerId = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $controllerId));
                    $url = Url::to(["/$id/$controllerId/index"]);
                    echo " - " . Html::a('🔗 Acessar', $url, ['target' => '_blank']);
                }
            ?>
        </li>
    <?php endforeach; ?>
</ul>

<?= \yii\helpers\Html::a('⬅️ Voltar', ['index'], ['class' => 'btn btn-secondary']) ?>

<?php
$dataProvider = new ArrayDataProvider([
    'allModels' => array_map(fn($t) => ['nome' => $t], $tabelas),
    'pagination' => false,
]);
?>

<h1><?= Html::encode($this->title) ?></h1>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'nome',
            'label' => 'Nome da Tabela',
            'format' => 'raw',
            'value' => fn($model) => Html::encode($model['nome']),
        ],
        [
            'attribute' => 'nome',
            'label' => 'Gerar CRUD',
            'format' => 'raw',
            'value' => function($model) use ($id) {
                $tabela = $model['nome'];
                $url = \yii\helpers\Url::to(['modulo/gerar-crud', 'tabela' => $tabela, 'modulo' => $id]);
                return \yii\helpers\Html::a('⚙️ Gerar CRUD', $url, ['class' => 'btn btn-sm btn-primary']);
            },
        ],

    ],
]); ?>
