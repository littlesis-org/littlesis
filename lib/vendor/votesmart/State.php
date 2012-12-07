<?php

/**
 * State class handles all state data
 * 
 * Copyright 2007 Project Vote Smart
 * Distributed under the BSD License
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 */

require_once("config.php");
require_once("Common.php");

class State extends Common {
        
        public function getStateIDs() {
                /**
                 * Returns a list of states and their 2 digit IDs
                 */
                
                $iface = "/State.getStateIDs";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getState($state_id) {
                /**
                 * Returns detailed state information
                 */
                
                $iface = "/State.getState";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&stateId=" . $state_id;
                
                return $this->getXml($iface, $args);
                
        }
        
}