<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author GOintegro devs
 */

namespace GoIntegro\Hateoas\Config;

class Resources implements ResourcesConfig
{    
    protected $resources = [];
    
    public function __construct(array $config = array())
    {
    	foreach ($config as $r) {
            $this->resources[$r['type']] = (object)$r;
            
            if (!empty($r['defaults'])) {
                $this->resources[$r['type']]->defaults = (object)$r['defaults'];
            }
        }
            
    }
    
    public function getAll()
    {
        return $this->resources;
    }
    
    public function get($resourceName)
    {
        $resource = null;
        
        if (!empty($this->resources[$resourceName])) {
            $resource = $this->resources[$resourceName];
        }
        
        return $resource;
    }
}

