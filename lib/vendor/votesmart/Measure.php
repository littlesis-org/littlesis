<?php

/**
 * Measure Class - for finding and retrieving details on Ballot Measures
 * 
 * Copyright 2008 Project Vote Smart
 * Distributed under the BSD License
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 */

require_once("config.php");
require_once("Common.php");

class Measure extends Common {
        
        public function getMeasure($measure_id) {
                /**
                 * Returns a ballot measure's details
                 */
                
                $iface = "/Measure.getMeasure";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&measureId=" . $measure_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getMeasuresByYearState($year, $state_id) {
                /**
                 * Returns a list of ballot measures that fit the criteria
                 */
                
                $iface = "/Measure.getMeasuresByYearState";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&stateId=" . $election_id . "&year=" . $year;
                
                return $this->getXml($iface, $args);
                
        }
        
}