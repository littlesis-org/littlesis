<?php

/**
 * Officials class handles fetching lists of incumbents
 * 
 * Copyright 2007 Project Vote Smart
 * Distributed under the BSD License
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 */

require_once("config.php");
require_once("Common.php");

class Officials extends Common {
        
        public function getByOfficeState($office_id, $state_id = 'NA') {
                /**
                 * Returns a list of incumbents that fit the criteria
                 */
                
                $iface = "/Officials.getByOfficeState";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&officeId=" . $office_id . "&stateId=" . $state_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getByLastname($lastname) {
                /**
                 * Searches for incumbents with exact lastname matches
                 */
                
                $iface = "/Officials.getByLastname";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&lastName=" . $lastname;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getByLevenstein($lastname) {
                /**
                 * Searches for incumbents with fuzzy lastname match
                 */
                
                $iface = "/Officials.getByLevenstein";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&lastName=" . $lastname;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getByElection($election_id) {
                /**
                 * Returns incumbents in the provided election_id
                 */
                
                $iface = "/Officials.getByElection";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&electionId=" . $election_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getByDistrict($district_id) {
                /**
                 * Returns incumbents in the provided district_id
                 */
                
                $iface = "/Officials.getByDistrict";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&districtId=" . $district_id;
                
                return $this->getXml($iface, $args);
                
        }
        
}