<?php

/**
 * CandidateBIo class handles candidate bio data
 * 
 * Copyright 2007 Project Vote Smart
 * Distributed under the BSD License
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 */

require_once("config.php");
require_once("Common.php");

class CandidateBio extends Common {
        
        public function getBio($can_id) {
                /**
                 * Returns basic bio details on a candidate
                 */
                
                $iface = "/CandidateBio.getBio";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $can_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getAddlBio($can_id) {
                /**
                 * Returns additional bio information for a candidate
                 * (i.e. Pet cat, favorite color)
                 */
                $iface = "/CandidateBio.getAddlBio";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $can_id;
                
                return $this->getXml($iface, $args);
                
        }
        
}