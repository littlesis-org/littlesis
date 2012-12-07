<?php

/**
 * Election class contains all the methods necessary to
 * gather election data
 * 
 * Copyright 2007 Project Vote Smart
 * Distributed under the BSD License
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 */

require_once("config.php");
require_once("Common.php");

class Election extends Common {
        
        public function getElection($election_id) {
                /**
                 * Returns detailed election information
                 */
                
                $iface = "/Election.getElection";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&electionId=" . $election_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getElectionByYearState($year, $state_id = 'NA') {
                /**
                 * returns a list of elections based on the criteria
                 */
                
                $iface = "/Election.getElectionByYearState";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&year=" . $year . "&stateId=" . $state_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getStageCandidates($election_id, $stage_id, $party = '') {
                /**
                 * Returns a list of candidates in the election and election stage provided
                 */
                
                $iface = "/Election.getStageCandidates";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&electionId=" . $election_id . "&stageId=" . $stage_id . "&party=" . $party;
                
                return $this->getXml($iface, $args);
                
        }
        
}