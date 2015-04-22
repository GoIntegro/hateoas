<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author GOintegro devs
 */

namespace GoIntegro\Hateoas\Config;


interface ResourcesConfig
{
    public function getAll();
    
    public function get($name);
}

