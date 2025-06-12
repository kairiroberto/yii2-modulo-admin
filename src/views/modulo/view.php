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

<h3>⚙️ Trecho do config/web.php</h3>
<pre style="background:#f0f0f0; padding:10px; border:1px solid #ccc; max-height:300px; overflow:auto;">
    <?= htmlentities($configWeb) ?>
</pre>

<p><a href="../index">⬅️ Voltar</a></p>
