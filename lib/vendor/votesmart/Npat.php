<?php

/**
 * Npat class contains all the methods necessary 
 * 
 * Copyright 2007 Project Vote Smart
 * Distributed under the BSD License
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 */

require_once("config.php");
require_once("Common.php");

class Npat extends Common {
        
        public function getNpat($can_id) {
                /**
                 * Returns the most recent NPAT filled out by a candidate
                 */
                
                $iface = "/Npat.getNpat";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $can_id;
                
                return $this->getXml($iface, $args);
                
        }
        
}