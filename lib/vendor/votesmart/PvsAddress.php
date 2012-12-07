<?php

/**
 * Address class contains all the methods necessary 
 * 
 * Copyright 2007 Project Vote Smart
 * Distributed under the BSD License
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 */

require_once("config.php");
require_once("Common.php");

class PvsAddress extends Common {
        
        public function getCampaign($can_id) {
                /**
                 * Returns a campaign office's contact information
                 */
                
                $iface = "/Address.getCampaign";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $can_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getCampaignWebAddress($can_id) {
                /**
                 * Returns a campaign office's contact information
                 */
                
                $iface = "/Address.getCampaignWebAddress";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $can_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getCampaignByElection($election_id) {
                /**
                 * Returns a campaign office's contact information
                 */
                
                $iface = "/Address.getCampaignByElection";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&electionId=" . $election_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getOffice($can_id) {
                /**
                 * Returns an incumbent office's contact information
                 */
                
                $iface = "/Address.getOffice";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $can_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getOfficeWebAddress($can_id) {
                /**
                 * Returns an incumbent office's contact information
                 */
                
                $iface = "/Address.getOfficeWebAddress";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $can_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getOfficeByOfficeState($office_id, $state_id = 'NA') {
                /**
                 * Returns a (sometimes)list of offices that fit office_id and state_id
                 */
                
                $iface = "/Address.getOfficeByOfficeState";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&officeId=" . $office_id . "&stateId=" . $state_id;
                
                return $this->getXml($iface, $args);
                
        }
        
}