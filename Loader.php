<?php
namespace DynamicLoader;

use Nette\Application\UI\Control;
use Nette\Caching\Cache;

/**
 * @property-read array $plugins Plugins
 * @property-write array $enablePlugins Array with plugins to load.
 * @property-read array $pluginsEnabled All enabled plugins.
 */
Class Loader extends Control
{
    const POSITION_HEAD         = 'head';
    const POSITION_BOTT         = 'botttom';
    const CACHE_KEY             = 'fritak/dynamicLoader';
    const MINIFY_KEY_JS         = 'fritak/dynamicLoader-js';
    const MINIFY_KEY_CSS        = 'fritak/dynamicLoader-css';
    
    public $defaultCSS          = self::POSITION_HEAD;
    public $defaultJS           = self::POSITION_HEAD;
    public $renderPosition      = self::POSITION_HEAD;
    public $directHeadPositions = [];
    public $directBottPositions = [];
    public $enabledPlugins      = [];
    public $addedPlugins        = [];
    public $pluginPath          = '';
    public $group               = '';
    public $minify              = FALSE;
    
    private $isDebugBar = FALSE;
    private $renderAll  = FALSE;
    private $bowerJson  = '';
    private $basePath   = '';
    private $plugins    = [];
    private $count      = 0;
    private $cacheStorage;
    
    public function __construct($config, $cacheStorage) 
    {
        $this->loadConfig($config);
        $this->cacheStorage = $cacheStorage;
        
        $cache = new Cache($cacheStorage);

        $encoded = $cache->load(self::CACHE_KEY, function()
        {
            $encode = [];
            $json = json_decode(file_get_contents($this->bowerJson));
            foreach($json->dependencies AS $plugin => $version)
            {
                $plugin = new Plugin($plugin, $this, $version); 
                $encode[] = $plugin->encode();
            }
            $encoded = json_encode($encode);
            return $encoded;
        });

        $decoded = json_decode($encoded);
        foreach($decoded AS $plugin)
        {
            $this->count++;
            $this->plugins[] = Plugin::decode($plugin, $this);
        }

        if(empty($config['disableBar']))
        {
            $this->initDebugBar();
        }
    }
    
    public function setEnablePlugins(array $val)
    {
        $this->addedPlugins = $val;
    }
    
    public function getPluginsEnabled()
    {
        if(!is_array($this->enabledPlugins))
        {
            throw new DynamicLoaderException('EnabledPlugins is not array.');
        }

        if($this->renderAll || count($this->enabledPlugins) < 1)
        {
            return $this->plugins;
        }
        $plugins = [];
        
        // If is array and group is set, return group, otherwise return all plugins. 
        if(is_array(current($this->enabledPlugins)))
        {
            if(isset($this->enabledPlugins[$this->group]))
            {
                $pluginsToPage = $this->enabledPlugins[$this->group];
            }
            else
            {
                return $this->plugins;
            }
        }
        else
        {
            $pluginsToPage = $this->enabledPlugins;
        }

        $pluginsToPage = array_merge($this->addedPlugins, $pluginsToPage);
        foreach($this->plugins AS $plugin)
        {
            if(in_array($plugin->name, $pluginsToPage))
            {
                $plugins[] = $plugin;
            }
        }
        return $plugins;
    }
    
    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/loader.latte');
        
        $template->plugins   = $this->pluginsEnabled;
        $template->position  = $this->renderPosition;
        $template->group     = $this->group;
        $template->minify    = $this->minify;
        
        if(!empty($this->basePath))
        {
            $template->basePath = $this->basePath;
        }
        
        if($this->isDebugBar)
        {
            DynamicLoaderBar::addPlugins($this->pluginsEnabled);
        }
        
        if($this->minify)
        {
            $this->minifyPlugins();
        }
        
        $template->render();
    }
    
    public function loadConfig($config)
    {
        if(isset($config['renderAll']) && $config['renderAll'])
        {
            $this->renderAll = TRUE;
        }
        
        if(isset($config['defaultPlugins']))
        {
            $this->enabledPlugins = $config['defaultPlugins'];
        }
        
        if(isset($config['defaultCSS']))
        {
            $this->defaultCSS = $config['defaultCSS'];
        }
        
        if(isset($config['defaultJS']))
        {
            $this->defaultJS  = $config['defaultJS'];
        }
        
        if(isset($config['positionsHead']))
        {
            $this->directHeadPositions = $config['positionsHead'];
        }
        
        if(isset($config['positionsBott']))
        {
            $this->directBottPositions = $config['positionsBott'];
        }
        if(isset($config['basePath'])) 
        {
            $this->basePath = $config['basePath'];
        }
        
        if(isset($config['bowerJson']))
        {
            $this->bowerJson = $config['bowerJson'];
        }
        else
        {
            throw new DynamicLoaderException('Key "bowerJson" is mandatory. Please add bower json path to your config.');
        }
        
        if(isset($config['pluginPath']))
        {
            $this->pluginPath = $config['pluginPath'];
        }
        else
        {
            throw new DynamicLoaderException('Key "pluginPath" is mandatory. Please add plugin path to your config.');
        }
        
        $this->minify = isset($config['minify'])? $config['minify'] : FALSE;
    }
    
    public function minifyPlugins()
    {
        $cache = new Cache($this->cacheStorage);
        
        $css = $cache->load(self::MINIFY_KEY_CSS);
        $js  = $cache->load(self::MINIFY_KEY_JS);
        
        if(is_null($css))
        {
            foreach($this->pluginsEnabled AS $plugin)
            { 
                foreach($plugin->files AS $file)
                {
                    if($file->type == 'js')
                    {
                        //$js .= ' ' . \PHPWee\Minify::js(file_get_contents($file->path . $file->name)); -bug Minify-
                        $js .= ' ' . file_get_contents($file->path . $file->name); 
                        
                    }
                    else 
                    { 
                        //$css .= ' ' . \PHPWee\Minify::css(file_get_contents($file->path . $file->name)); -bug Minify-
                        $css .= ' ' . file_get_contents($file->path . $file->name); 
                        
                    }
                }
            }
            $cssFile = fopen($this->pluginPath . '/minify.css', 'w+');
            $jsFile  = fopen($this->pluginPath . '/minify.js',  'w+');

            fwrite($jsFile, $js);
            fwrite($cssFile, $css);

            fclose($cssFile);
            fclose($jsFile);
            
            $cache->save(self::MINIFY_KEY_CSS, $css);
            $cache->save(self::MINIFY_KEY_JS,  $js);
        }
        
        return TRUE;
    }
    
    /**
     * Inicializuje DEBUG bar.
     */
    private function initDebugBar()
    {
        if (!$this->isDebugBar)
        {
            $this->isDebugBar = TRUE;
            \Tracy\Debugger::getBar()->addPanel(new DynamicLoaderBar($this->count));
        }
    }
}
