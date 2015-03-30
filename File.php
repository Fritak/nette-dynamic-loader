<?php
namespace DynamicLoader;

Class File extends \Nette\Object
{
    const TYPE_CSS = 'css';
    const TYPE_JS  = 'js';
    
    /** @var \DynamicLoader\Plugin */
    public $parent;
    public $position;
    public $name;
    public $path;
    public $type;
    
    public function __construct($name, $path, $type, &$parent) 
    {
        $this->name     = $name;
        $this->path     = $path;
        $this->type     = $type;
        $this->parent   = $parent;
        $this->setPosition();
    }
    
    public function setPosition()
    {
        if($this->type == self::TYPE_CSS)
        {
            $this->position = $this->parent->parent->defaultCSS;
        }
        
        if($this->type == self::TYPE_JS)
        {
            $this->position = $this->parent->parent->defaultJS;
        }
            
        if(in_array($this->name, $this->parent->parent->directHeadPositions))
        {
            $this->position = Loader::POSITION_HEAD;
        }


        if(in_array($this->name, $this->parent->parent->directBottPositions))
        {
            $this->position = Loader::POSITION_BOTT;
        }
    }
}
