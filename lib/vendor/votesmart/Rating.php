<?php

/**
 * Rating class contains all the methods necessary 
 * 
 * Copyright 2007 Project Vote Smart
 * Distributed under the BSD License
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 */

require_once("config.php");
require_once("Common.php");

class Rating extends Common {
        
        public function getCategories($state_id = 'NA') {
                /**
                 * Returns categories with ratings according to
                 * state_id(NA = fed)
                 */
                
                $iface = "/Rating.getCategories";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getSigList($category_id, $state_id = 'NA') {
                /**
                 * Returns a list of SIGs with ratings in category
                 * and state
                 */
                
                $iface = "/Rating.getSigList";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&categoryId=" . $category_id . "&stateId=" . $state_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getSig($sig_id) {
                /**
                 * Returns detailed SIG information
                 */
                
                $iface = "/Rating.getSig";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&sigId=" . $sig_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getCandidateRating($candidate_id, $sig_id) {
                /**
                 * Returns an SIG's rating on a specific candidate
                 */
                
                $iface = "/Rating.getCandidateRating";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $candidate_id . "&sigId=" . $sig_id;
                
                return $this->getXml($iface, $args);
                
        }
        
}