<h1>📦 Módulos Registrados</h1>
<table border="1" cellpadding="5">
<tr><th>Nome</th><th>Classe</th></tr>
<?php foreach ($modulos as $nome => $config): ?>
<tr>
    <td><?= $nome ?> [<a href="<?= \yii\helpers\Url::to(['modulo/view', 'id' => $nome]) ?>">🔍 Ver Estrutura</a>]</td>
    <td><?= $config['class'] ?></td>
</tr>
<?php endforeach; ?>
</table>
<a href="add">➕ Adicionar Novo Módulo</a>