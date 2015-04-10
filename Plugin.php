<?php
namespace DynamicLoader;

Class Plugin extends \Nette\Object
{
    public $name;
    public $verison;
    public $files = [];
    /** @var \DynamicLoader\Loader */
    public $parent;
    
    public function __construct($name, &$parent, $verison = '', $scanDir = TRUE) 
    {
        $this->name     = $name;
        $this->verison  = $verison;
        $this->parent   = $parent;
        $cssPath = $this->parent->pluginPath . $this->name . '/css/';
        $jsPath  = $this->parent->pluginPath . $this->name . '/js/';
        
        if($scanDir)
        {
            if(is_dir($cssPath))
            {
                $css = scandir($cssPath);
                foreach($css AS $file)
                { 
                    if(is_file($cssPath . $file))
                    {
                        $this->files[] = new File($file, $cssPath, File::TYPE_CSS, $this);
                    }
                }
            }
            if(is_dir($jsPath))
            {
                $js = scandir($jsPath);
                foreach($js AS $file)
                { 
                    if(is_file($jsPath . $file))
                    {
                        $this->files[] = new File($file, $jsPath, File::TYPE_JS, $this);
                    }
                }
            }
        }
    }
    
    public function encode() 
    {
        $files = [];
        foreach($this->files AS $file)
        {
            $files[] = $file->toArray();
        }
        return ['n' => $this->name, 'v' => $this->verison, 'f' => $files];
    }
    
    public static function decode($data, &$parent) 
    {
        $plugin = new Plugin($data->n, $parent, $data->v, FALSE);
        
        foreach($data->f AS $file)
        {
            $plugin->files[] = new File($file->name, $file->path, $file->type, $plugin);
        }
        
        return $plugin;
    }
}
