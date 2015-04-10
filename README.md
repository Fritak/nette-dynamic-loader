## nette-dynamic-loader
This is a dummy dynamic loader for nette. 

##Getting started

1. Download all plugins via bower, install in PATH: PLUGIN_NAME/css and PLUGIN_NAME/js (Unfortunately, there is currently no other path possible)
You can do it nicely with Bower installer (bower.json):
``` json
  "dependencies": {
    "jquery": "~2.1.3",
    "jquery-ui": "~1.11.4",
    "bootstrap": "~3.3.4",
  },
  "install": {
    "path": {
        "css": "plugins/{name}/css",
        "js": "plugins/{name}/js",
        "/[sc|le]ss$/": "plugins/{name}/css",
        "eot": "plugins/{name}/fonts",
        "svg": "plugins/{name}/fonts",
        "ttf": "plugins/{name}/fonts",
        "woff": "plugins/{name}/fonts",
        "woff2": "plugins/{name}/fonts",
        "otf": "plugins/{name}/fonts",
        "png":  "plugins/{name}/css/images"
    }
```

2. Prefered install method is via composer 

``` sh
composer require fritak/nette-dynamic-loader
```

3. Register at config.neon with parameters a for caching
``` php
parameters:
    loader:
        bowerJson:  '/var/www/bower.json' # Path to your bower file
        pluginPath: '/var/www/plugins/'   # Path to installed plugins (see item 0)
services:
	- DynamicLoader\Loader(%loader%, @cacheStorage)
``` 

4. Create component (eg. in BasePresenter)
``` php
    use DynamicLoader\Loader;

    public function createComponentHeadLoader() 
    {
        $component = clone $this->context->getByType('DynamicLoader\Loader');
        $component->renderPosition = Loader::POSITION_HEAD;
        return $component;
    }
    
    public function createComponentBottomLoader() 
    {
        $component = clone $this->context->getByType('DynamicLoader\Loader');
        $component->renderPosition = Loader::POSITION_BOTT;
        return $component;
    }
```

5. Add control to a template (eg. @layout)
``` php
{control dynamicLoader}
```

6. And that's it! You can control it with following config:

``` php
parameters:
    loader:
        defaultCSS: DynamicLoader\Loader::POSITION_HEAD #Defaut CSS position
        defaultJS:  DynamicLoader\Loader::POSITION_BOTT #Defaut JS  position
        renderAll:  1                                   # Render ALL added plugins
        bowerJson:  '/var/project/www/bower.json'       # Path to your bower file
        basePath:   '/project/www'                      # If you have different basePath than is default
        pluginPath: '/var/project/www/plugins/'         # Path to installed plugins (see item 0)
        disableBar: 1                                   # You can disable debug bar
        positionsHead:                                  # You can set plugins positions in HEAD OR BOTT directly:
            - 'bootstrap.min.css'
        positionsBott:
            - 'bootstrap.min.js'
``` 
7. You can set default plugins global
``` php
        defaultPlugins:                                 
            - 'jquery'
            - 'jquery-ui'
            - 'bootstrap'
``` 
8. Or in groups. But then you have to set GROUP to a component.
``` php
        defaultPlugins:                                 
            front:
                - 'jquery'
            backEnd: 
                - 'jquery'
                - 'jquery-ui'
                - 'bootstrap'
``` 

9. If you set defaultPlugins, others won't be loaded. So you have to set them manually in presenters (eg. in HomepagePresenter):
``` php
    public function __construct() 
    {
        $this->enablePlugins = ['jquery-ui'];
        parent::__construct();
    }
``` 

10. And edit basePresenter:
``` php
    public $enablePlugins = [];

    public function createComponentHeadLoader() 
    {
        $component = clone $this->context->getByType('\DynamicLoader\Loader');
        $component->renderPosition = Loader::POSITION_HEAD;
        $component->enablePlugins = $this->enablePlugins;
        $component->group = 'front';
        return $component;
    }
    
    public function createComponentBottomLoader() 
    {
        $component = clone $this->context->getByType('\DynamicLoader\Loader');
        $component->renderPosition = Loader::POSITION_BOTT;
        $component->enablePlugins = $this->enablePlugins;
        $component->group = 'front';
        return $component;
    }
``` 

## Links

* [Nette framework](http://nette.org/)
* [Bower](http://bower.io/)
* [Bower installer](https://github.com/blittle/bower-installer)
* [Composer](https://getcomposer.org/)