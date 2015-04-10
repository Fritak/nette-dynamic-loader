<?php
namespace DynamicLoader;

Class DynamicLoaderBar extends \Nette\Object implements \Nette\Diagnostics\IBarPanel
{
    public static $img = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAAnNCSVQICFXsRgQAAAAJcEhZcwAAAG8AAABvAfGi3EMAAAAZdEVYdFNvZnR3YXJlAHd3dy5pbmtzY2FwZS5vcmeb7jwaAAAA70lEQVQYGQXBoU6VAQCA0XNn1+B0/AZQ56aBQsankCS3CEWKFjbvDLCfQuMJyGSd0ngF2XwDwI0bSLBJMNx9niNJvDZzZuaL77Y8SRKJiV3/JKNRsjBLIh44lSSjUZIcJmJXkmQ0SpJshBfuJbm06aFHtv2RX9atWeZAkguPk8SSqSXHFnb4Kck0SRKr7iQnzCUZkiQxSHLFrSQrSZJ4Jsmc35JsJUliKskPjiS58TxJPDWXZJ83FpJ8sGoweO9aknuvxEfJucGdJEnyORJ78taxJElyahJJvLNmIUny1yeTRJJYtuPElWvffPUySf4DYtnJ78i3u1cAAAAASUVORK5CYIIf02f6e8fc6151b1713c399dca89d08b4"/>';    
    public static $plugins;
    public static $count;
    
    public function __construct($count) {
        self::$count = $count;
    }
    
    /**
     * 
     * @return string Vrátí TAB.
     */
    public function getTab()
    {
        return '<span title="DynamicLoader">' . self::$img . ' ' . count(self::$plugins) . '/' . self::$count . '</span>';
    }

    /**
     * 
     * @return string Vrátí panel.
     */
    public function getPanel()
    {
        $output = '<h1>Loaded files</h1>'
                . '<div style="color: red;"><b>Plugins are cached!</b></div><br />'
                . '<div class="nette-inner">'
                . '<table>'
                . '<thead>'
                . '<tr>'
                . '<th>Plugin name</th>'
                . '<th>File name</th>'
                . '<th>Position</th>'
                . '</tr>'
                . '</thead>'
                . '<tbody>';
        
        foreach(self::$plugins AS $plugin)
        {
            foreach($plugin->files AS $file)
            {
                $output .= '<tr class="no">'
                        . '<td>'. $file->parent->name .'</td>'
                        . '<td>'. $file->name .'</td>'
                        . '<td>'. $file->position .'</td>'
                        . '</tr>';
            }
        }
        
        return $output . '</tbody></table></div>';
    }
    

    public static function addPlugins(array $plugins)
    {
        self::$plugins = $plugins;
    }

}
