# yii2-modulo-admin

Este é um painel de administração de módulos para projetos em Yii2.

### Funcionalidades

- 📦 Lista todos os módulos registrados (`config/modules.php`)
- ➕ Permite adicionar novos módulos via interface
- 🔍 Visualiza a estrutura de cada módulo:
  - Module.php
  - Controllers
  - Models
  - Views
  - Layouts
- ⚙️ Exibe o conteúdo do `config/web.php`

### Instalação via Composer (repositório Git)

```bash
composer config repositories.yii2-modulo-admin vcs https://github.com/kairiroberto/yii2-modulo-admin
composer require roberto/yii2-modulo-admin:dev-main
