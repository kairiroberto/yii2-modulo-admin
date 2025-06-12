<?php

namespace roberto\moduloadmin\controllers;

use Yii;
use yii\web\Controller;
use yii\base\DynamicModel;

class ModuloController extends Controller {

    public function actionIndex() {
        $modulos = require Yii::getAlias('@app/config/modules.php');
        return $this->render('index', ['modulos' => $modulos]);
    }

    public function actionAdd()
    {
        $model = new DynamicModel(['nome', 'classe']);
        $model->addRule(['nome', 'classe'], 'required');
    
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $nome = $model->nome;
            $classe = $model->classe;
    
            $arquivo = Yii::getAlias('@app/config/modules.php');
            $modulos = require $arquivo;
    
            if (!isset($modulos[$nome])) {
                $modulos[$nome] = ['class' => $classe];
                file_put_contents($arquivo, "<?php\nreturn " . var_export($modulos, true) . ";\n");
            }
    
            return $this->redirect(['index']);
        }
    
        return $this->render('add', ['model' => $model]);
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

        $tabelas = Yii::$app->db->schema->getTableNames();

        return $this->render('view', [
                    'id' => $id,
                    'estrutura' => $estrutura,
                    'configWeb' => $configWeb,
                    'tabelas' => $tabelas,
        ]);
    }
    
    public function actionGerarCrud($tabela, $modulo)
    {
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($tabela))));
        $modelClass = "app\\modules\\$modulo\\models\\$className";
        $searchClass = "app\\modules\\$modulo\\models\\{$className}Search";
        $controllerClass = "app\\modules\\$modulo\\controllers\\{$className}Controller";
        $viewPath = "@app/modules/$modulo/views/" . str_replace('_', '-', strtolower($tabela));
    
        // Gera Model
        $model = new \yii\gii\generators\model\Generator();
        $model->tableName = $tabela;
        $model->modelClass = $className;
        $model->ns = "app\modules\\$modulo\models";
        $model->baseClass = 'yii\db\ActiveRecord';
    
        if ($model->validate()) {
            foreach ($model->generate() as $file) {
                $dir = dirname($file->path);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents($file->path, $file->content);
            }
        }
    
        // Gera CRUD
        $crud = new \yii\gii\generators\crud\Generator();
        $crud->modelClass = $modelClass;
        $crud->searchModelClass = $searchClass;
        $crud->controllerClass = $controllerClass;
        $crud->viewPath = $viewPath;
    
        if ($crud->validate()) {
            foreach ($crud->generate() as $file) {
                $dir = dirname($file->path);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents($file->path, $file->content);
            }
        }
    
        Yii::$app->session->setFlash('success', "CRUD para '{$tabela}' gerado com sucesso no módulo '$modulo'!");
        return $this->redirect(['view', 'id' => $modulo]);
    }
    
}
