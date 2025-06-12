<?php

namespace roberto\moduloadmin\controllers;

use Yii;
use yii\web\Controller;

class ModuloController extends Controller {

    public function actionIndex() {
        $modulos = require Yii::getAlias('@app/config/modules.php');
        return $this->render('index', ['modulos' => $modulos]);
    }

    public function actionAdd() {
        if (Yii::$app->request->isPost) {
            $nome = Yii::$app->request->post('nome');
            $classe = Yii::$app->request->post('classe');

            $arquivo = Yii::getAlias('@app/config/modules.php');
            $modulos = require $arquivo;

            if (!isset($modulos[$nome])) {
                $modulos[$nome] = ['class' => $classe];
                file_put_contents($arquivo, "<?php\nreturn " . var_export($modulos, true) . ";\n");
            }

            return $this->redirect(['index']);
        }

        return $this->render('add');
    }

    public function actionView($id) {
        $basePath = Yii::getAlias("@app/modules/{$id}");
        
        // Cria a pasta do módulo se não existir
        @mkdir($basePath, 0777, true);

        // Criar diretórios se não existirem
        @mkdir("$basePath/controllers", 0777, true);
        @mkdir("$basePath/models", 0777, true);
        @mkdir("$basePath/views/layouts", 0777, true);

        // Criar Module.php básico se não existir
        $moduleFile = "$basePath/Module.php";
        if (!file_exists($moduleFile)) {
            $codigo = "<?php
namespace app\\modules\\$id;

use yii\\base\\Module;

class Module extends Module
{
    public \$controllerNamespace = 'app\\\\modules\\\\$id\\\\controllers';

    public function init()
    {
        parent::init();
    }
}
";
            file_put_contents($moduleFile, $codigo);
        }

        $estrutura = [
            'Module.php' => is_file("$basePath/Module.php"),
            'controllers' => scandir("$basePath/controllers"),
            'models' => scandir("$basePath/models"),
            'views' => scandir("$basePath/views"),
            'views/layouts' => scandir("$basePath/views/layouts"),
        ];

        $configWeb = file_exists(Yii::getAlias('@app/config/web.php')) ? file_get_contents(Yii::getAlias('@app/config/web.php')) : null;

        return $this->render('view', [
                    'id' => $id,
                    'estrutura' => $estrutura,
                    'configWeb' => $configWeb,
        ]);
    }
}
