<?php
namespace DynamicLoader;

use Nette\Application\UI\Control;

/**
 * @property-read array $plugins Plugins
 * @property-write array $enablePlugins Array with plugins to load.
 * @property-read array $pluginsEnabled All enabled plugins.
 */
Class Loader extends Control
{
    const POSITION_HEAD         = 'head';
    const POSITION_BOTT         = 'botttom';
    
    public $defaultCSS          = self::POSITION_HEAD;
    public $defaultJS           = self::POSITION_HEAD;
    public $renderPosition      = self::POSITION_HEAD;
    public $directHeadPositions = [];
    public $directBottPositions = [];
    public $enabledPlugins      = [];
    public $addedPlugins        = [];
    public $pluginPath          = '';
    public $group               = '';
    
    private $isDebugBar = FALSE;
    private $renderAll  = FALSE;
    private $bowerJson  = '';
    private $basePath   = '';
    private $plugins    = [];
    
    public function __construct($config) 
    {
        $this->loadConfig($config);
        
        if(empty($config['disableBar']))
        {
            $this->initDebugBar();
        }
        
        $json = json_decode(file_get_contents($this->bowerJson));
        foreach($json->dependencies AS $plugin => $version)
        {
            $this->plugins[] = new Plugin($plugin, $version, $this); 
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
        
        if(!empty($this->basePath))
        {
            $template->basePath = $this->basePath;
        }
        
        if($this->isDebugBar)
        {
            DynamicLoaderBar::addPlugins($this->pluginsEnabled);
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
    }
    
    /**
     * Inicializuje DEBUG bar.
     */
    private function initDebugBar()
    {
        if (!$this->isDebugBar)
        {
            $this->isDebugBar = TRUE;
            \Tracy\Debugger::getBar()->addPanel(new DynamicLoaderBar);
        }
    }
}
