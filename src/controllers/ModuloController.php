<?php

namespace roberto\moduloadmin\controllers;

use Yii;
use yii\web\Controller;

class ModuloController extends Controller
{
    public function actionIndex()
    {
        $modulos = require Yii::getAlias('@app/config/modules.php');
        return $this->render('index', ['modulos' => $modulos]);
    }

    public function actionAdd()
    {
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

    public function actionView($id)
    {
        $basePath = Yii::getAlias("@app/modules/{$id}");
        if (!is_dir($basePath)) {
            throw new \yii\web\NotFoundHttpException("Módulo '{$id}' não encontrado.");
        }

        $estrutura = [
            'Module.php' => is_file("{$basePath}/Module.php"),
            'controllers' => is_dir("{$basePath}/controllers") ? scandir("{$basePath}/controllers") : [],
            'models' => is_dir("{$basePath}/models") ? scandir("{$basePath}/models") : [],
            'views' => is_dir("{$basePath}/views") ? scandir("{$basePath}/views") : [],
            'views/layouts' => is_dir("{$basePath}/views/layouts") ? scandir("{$basePath}/views/layouts") : [],
        ];

        $configWeb = file_exists(Yii::getAlias('@app/config/web.php')) ? file_get_contents(Yii::getAlias('@app/config/web.php')) : null;

        return $this->render('view', [
            'id' => $id,
            'estrutura' => $estrutura,
            'configWeb' => $configWeb,
        ]);
    }
}