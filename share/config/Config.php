<?php
/*
 * Kristoffer Langeland Knudsen
 * rainbowponyprincess@gmail.com
 *
 * LBP Config File
 */

namespace Config; 


class Config {

    private $json;
    
    public function __construct($filename = 'settings.json') {
        
        $filename = dirname(__FILE__) . '/' . $filename;

        $this->json = json_decode(file_get_contents($filename));
    }

    // --------------------------------------------- //

    public function __get($attrib) {
        return $this->json->$attrib;        
    }
}



?>