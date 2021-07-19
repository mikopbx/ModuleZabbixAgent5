# MikoPBX extension module template #

*Read this in other languages: [English](README.md), [Русский](README.ru.md).*

## Module description ##

You can use this template to make a new extension for MikoPBX. I contains a web UI, with JS, CSS and PHP classes and database.


## Folder structure ##

* **app** folder -  for MVC part of project in compliance with the [Phalcon app Structure](https://docs.phalcon.io/3.4/en/tutorial-basic#file-structure)  
* **bin** folder  - for binary executable files
* **coreapi** folder  - for REST API classes
* **db** folder - module database is created automatically within the installation process according to ORM Models annotations
* **lib** folder  -  for main program classes
* **messages** folder - for translation files, like ru.php, en.php ...
* **Models**  folder - ORM models are described  compliance with the [Phalcon Models Structure](https://docs.phalcon.io/3.4/en/db-models)
* **public** folder  - for public accessible files, i.e. js, css, images
* **setup** folder - for installation and uninstallation classes, and additional firewall rules

### Prepare template ###
You can use [this bash script](https://github.com/mikopbx/ExtensionsDevTools) to prepare your copy or you may follow the next steps:

1. Make new repository and clone your new one to Phpstorm project. 
2. Make group renaming in folder:
 * `ModuleZabbixAgent5` to new unique module ID, i.e. `MyCompanyMyNewModule4PBX`
 * `module_zabbix_agent5` to `MyCompanyMod4PBX_tpl`
 * `module-zabbix-agent5` to `my-company-module-4-pbx`  

3. Change filename of controller class in folder `app/controllers`  \
from `ModuleZabbixAgent5Controller.php` to `MyCompanyMyNewModule4PBXController.php` 

4. Change filename of form class in folder `app/forms` \
from `ModuleZabbixAgent5Form.php` to `MyCompanyMyNewModule4PBXForm.php` 

5. Rename and edit classes in `Models` folder for future database according to the [Phalcon app Structure](https://docs.phalcon.io/3.4/en/db-models) and [Phalcon models annotations](https://docs.phalcon.io/3.4/en/db-models-metadata#annotations-strategy)

6. Rename JS и CSS files in folder `public/src`\
from `my-company-module-4-pbx-index.js` to `my-company-module-4-pbx.css`

7. If your future module has to open any firewall ports, describe it at `setup/FirewallRules.php` or just delete this file, if you don't need it.

8. Modify `module.json`, change module unique ID, developer’s name, version information and update/add functions to install your future module correctly.

 

### File PbxExtensionSetup.php ###
This file must have at least one `function __construct()`. \
You can inherit the other function from parent class [PbxExtensionBase](https://github.com/mikopbx/core/blob/master/www/src/modules/PbxExtensionBase.php)
  
### ORM Models classes ###
You must use namespace like  `Modules\<ModuleUniqueID>\Models`.  Every file has to extend `ModuleBaseClass` 
In class `ModuleBaseClass` you must have the following method: `getRepresent()`

### Controllers classes ###
At every controller public method you must specify the module view file template path: \
`$this->view->pick( "{$modulesDir}/<ModuleUniqueID>/app/views/index" );`

### Translations ###
Your module has to have at 1 file e.g.: `en.php` with at least 2 translated phrases being there in the file:
* **Breadcrumb<ModuleUniqueID>** - Your module name
* **SubHeader<ModuleUniqueID>** - Your module description for subheader
	
		
### Useful links ###
Before creating a new module you may read some docs:

* [Semntic UI](https://semantic-ui.com)
* [Phalcon PHP framework](https://docs.phalcon.io/3.4/en/introduction)
* [MikoPBX Wiki](https://wiki.mikopbx.com)
* [Asterisk Wiki](https://wiki.asterisk.org/wiki/display/AST/Home)

Code style guides:

* [Airbnb JavaScript Style Guide](https://github.com/airbnb/javascript)
* [PSR-1: Basic Coding Standard](https://www.php-fig.org/psr/psr-1/)

### Questions ###
You are welcome to our telegram channel for developers [@mikopbx_dev](https://t.me/joinchat/AAPn5xSqZIpQnNnCAa3bBw)
