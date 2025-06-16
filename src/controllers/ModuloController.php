<?php

namespace kairiroberto\moduloadmin\controllers;

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
    
            return $this->redirect(['view', 'id' => $nome]);
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
        @mkdir("$basePath/config", 0777, true);
        @mkdir("$basePath/views/default", 0777, true);
        
        $this->criarConfigWeb($basePath, $id);
        $this->criarModule($basePath, $id);
        $this->criarAdminUser($basePath, $id);
        $this->criarLoginForm($basePath, $id);
        $this->criarDefaultLogin($basePath, $id);
        $this->criarDefaultIndex($basePath, $id);
        $this->criarDefaultError($basePath, $id);
        $this->criarDefaultController($basePath, $id);
        $this->criarLayoutMain($basePath, $id);

        $estrutura = [
            'Module.php' => is_file("$basePath/Module.php"),
            'controllers' => scandir("$basePath/controllers"),
            'models' => scandir("$basePath/models"),
            'views' => scandir("$basePath/views"),
            'views/layouts' => scandir("$basePath/views/layouts"),
            'config' => scandir("$basePath/config"),
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
    
    public function criarModule($basePath, $id) {
        // Criar Module.php básico se não existir
        $moduleFile = "$basePath/Module.php";
        if (!file_exists($moduleFile)) {
            $codigo = "<?php
            
namespace app\\modules\\$id;

use Yii;
use yii\\base\\Module as BaseModule;

class Module extends BaseModule
{
    public \$controllerNamespace = 'app\\\\modules\\\\$id\\\\controllers';
    public \$layout = '@app/modules/$id/views/layouts/main';

    public function init()
    {
        parent::init();

        // Carrega configurações próprias do módulo
        \$configFile = __DIR__ . '/config/web.php';
        if (file_exists(\$configFile)) {
            \$config = require \$configFile;

            if (isset(\$config['components'])) {
                foreach (\$config['components'] as \$compId => \$component) {
                    Yii::\$app->set(\$compId, \$component);
                }
            }

            if (isset(\$config['aliases'])) {
                foreach (\$config['aliases'] as \$alias => \$path) {
                    Yii::setAlias(\$alias, \$path);
                }
            }
        }
    }
}
";
            file_put_contents($moduleFile, $codigo);
        }
    }
    
    public function criarConfigWeb($basePath, $id) {
        $webConfigPath = "$basePath/config/web.php";
        if (!file_exists($webConfigPath)) {
            $webConfigContent = "<?php
    
    return [
        'components' => [
            'user' => [
                'class' => yii\\web\\User::class,
                'identityClass' => 'app\\\\modules\\\\$id\\\\models\\\\AdminUser',
                'enableAutoLogin' => false,
                'loginUrl' => ['$id/default/login'],
            ],
            'errorHandler' => [
                'class' => yii\\web\\ErrorHandler::class,
                'errorAction' => '$id/default/error',
            ],
        ],
        'aliases' => [
            '@{$id}Assets' => '@app/modules/$id/assets',
        ],
    ];
    ";
            file_put_contents($webConfigPath, $webConfigContent);
        }
    }
    
    private function criarAdminUser($basePath, $id) {
        $adminUserPath = "$basePath/models/AdminUser.php";
        if (!file_exists($adminUserPath)) {
            $adminUserContent = "<?php

namespace app\\modules\\$id\\models;

use yii\\web\\IdentityInterface;

class AdminUser implements IdentityInterface
{
    public \$id;
    public \$username;
    public \$password;
    public \$authKey;

    private static \$users = [
        '100' => [
            'id' => '100',
            'username' => 'admin',
            'password' => '123456',
            'authKey' => 'adminkey',
        ],
    ];

    public static function findIdentity(\$id)
    {
        return isset(self::\$users[\$id]) ? new static(self::\$users[\$id]) : null;
    }

    public static function findIdentityByAccessToken(\$token, \$type = null)
    {
        foreach (self::\$users as \$user) {
            if (\$user['authKey'] === \$token) {
                return new static(\$user);
            }
        }
        return null;
    }

    public static function findByUsername(\$username)
    {
        foreach (self::\$users as \$user) {
            if (strcasecmp(\$user['username'], \$username) === 0) {
                return new static(\$user);
            }
        }
        return null;
    }

    public function __construct(\$config = [])
    {
        foreach (\$config as \$k => \$v) {
            \$this->\$k = \$v;
        }
    }

    public function getId() { return \$this->id; }
    public function getAuthKey() { return \$this->authKey; }
    public function validateAuthKey(\$authKey) { return \$this->authKey === \$authKey; }
    public function validatePassword(\$password) { return \$this->password === \$password; }
}
";
            file_put_contents($adminUserPath, $adminUserContent);
        }

    }
    
    private function criarLoginForm($basePath, $id) {
        $loginFormPath = "$basePath/models/LoginForm.php";
        if (!file_exists($loginFormPath)) {
            $loginFormContent = "<?php

namespace app\\modules\\$id\\models;

use Yii;
use yii\\base\\Model;

class LoginForm extends Model
{
    public \$username;
    public \$password;
    public \$rememberMe = false;

    private \$_user = false;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword(\$attribute, \$params)
    {
        \$user = \$this->getUser();

        if (!\$user || !\$user->validatePassword(\$this->password)) {
            \$this->addError(\$attribute, 'Usuário ou senha inválidos.');
        }
    }

    public function login()
    {
        if (\$this->validate()) {
            return Yii::\$app->user->login(\$this->getUser());
        }
        return false;
    }

    protected function getUser()
    {
        if (\$this->_user === false) {
            \$this->_user = AdminUser::findByUsername(\$this->username);
        }
        return \$this->_user;
    }
}
";
            file_put_contents($loginFormPath, $loginFormContent);
        }
    }
    
    private function criarDefaultLogin($basePath, $id) {
        $loginViewPath = "$basePath/views/default/login.php";
        if (!file_exists($loginViewPath)) {
            @mkdir(dirname($loginViewPath), 0777, true);
            $loginViewContent = "<?php
use yii\\helpers\\Html;
use yii\\widgets\\ActiveForm;

\$this->title = 'Login';
?>

<h1>Login</h1>

<?php \$form = ActiveForm::begin(); ?>

<?= \$form->field(\$model, 'username') ?>
<?= \$form->field(\$model, 'password')->passwordInput() ?>
<?= \$form->field(\$model, 'rememberMe')->checkbox() ?>

<div class=\"form-group\">
    <?= Html::submitButton('Entrar', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
";
            file_put_contents($loginViewPath, $loginViewContent);
        }
    }
    
    private function criarDefaultController($basePath, $id) {
        $controllerPath = "$basePath/controllers/DefaultController.php";
        if (!file_exists($controllerPath)) {
            $controllerContent = "<?php

namespace app\\modules\\$id\\controllers;

use Yii;
use yii\\web\\Controller;
use yii\\filters\\AccessControl;
use app\\modules\\$id\\models\\LoginForm;

class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'logout'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return \$this->render('index');
    }

    public function actionLogin()
    {
        \$model = new LoginForm();
        if (\$model->load(Yii::\$app->request->post()) && \$model->login()) {
            return \$this->redirect(['index']);
        }

        return \$this->render('login', ['model' => \$model]);
    }

    public function actionLogout()
    {
        Yii::\$app->user->logout();
        return \$this->redirect(['login']);
    }

    public function actionError()
    {
        return \$this->renderContent('<h1>Erro</h1><p>Ocorreu um erro ao acessar esta página.</p>');
    }
}
";
            file_put_contents($controllerPath, $controllerContent);
        }
    }

    private function criarDefaultIndex($basePath, $id) {
        $indexViewPath = "$basePath/views/default/index.php";
        if (!file_exists($indexViewPath)) {
            $indexContent = "<h1>Módulo <code>$id</code> carregado com sucesso!</h1>";
            file_put_contents($indexViewPath, $indexContent);
        }
    }
    
    private function criarDefaultError($basePath, $id) {
        $indexViewPath = "$basePath/views/default/error.php";
        if (!file_exists($indexViewPath)) {
            $indexContent = "<h1>Erro: <code>$id</code>!</h1>";
            file_put_contents($indexViewPath, $indexContent);
        }
    }
    
    private function criarLayoutMain($basePath, $id) {
        $layoutOrigem = Yii::getAlias('@app/modules/moduloadmin/views/layouts/main1.php');
        $layoutDestino = "$basePath/views/layouts/main.php";
    
        if (file_exists($layoutOrigem) && !file_exists($layoutDestino)) {
            copy($layoutOrigem, $layoutDestino);
        }
    }
    
}
