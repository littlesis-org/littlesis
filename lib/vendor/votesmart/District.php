<?php

/**
 * District class contains all the methods necessary 
 * 
 * Copyright 2007 Project Vote Smart
 * Distributed under the BSD License
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 */

require_once("config.php");
require_once("Common.php");

class District extends Common {
        
        public function getByOfficeState($office_id, $state_id = 'NA', $district_name = '') {
                /**
                 * Returns districts service the office and state provided
                 */
                
                $iface = "/District.getByOfficeState";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&officeId=" . $office_id . "&stateId=" . $state_id . "&districtName=" . $district_name;
                
                return $this->getXml($iface, $args);
                
        }
        
}