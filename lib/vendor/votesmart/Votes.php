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

class Votes extends Common {
        
        public function getCategories($year, $state_id = 'NA') {
                /**
                 * Returns a list of categories with votes assigned
                 * for the year and state provided
                 */
                
                $iface = "/Votes.getCategories";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&year=" . $year . "&stateId=" . $state_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getBill($bill_id) {
                /**
                 * Returns detailed bill information
                 */
                
                $iface = "/Votes.getBill";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&billId=" . $bill_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getBillAction($act_id) {
                /**
                 * Returns a bill's action/stage(signed by prez, passed senate)
                 * detailed information
                 */
                
                $iface = "/Votes.getBillAction";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&actionId=" . $act_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getBillActionVotes($act_id) {
                /**
                 * Get's a bill's action's votes
                 */
                
                $iface = "/Votes.getBillActionVotes";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&actionId=" . $act_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getBillActionVoteByOfficial($act_id, $can_id) {
                /**
                 * Gets a single candidate's vote on a bill
                 */
                
                $iface = "/Votes.getBillActionVoteByOfficial";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&actionId=" . $act_id . "&candidateId=" . $can_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getBillsByCategoryYearState($category_id, $year, $state = 'NA') {
                /**
                 * Gets a list of bills that fit the criteria
                 */
                
                $iface = "/Votes.getBillsByCategoryYearState";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&categoryId=" . $category_id . "&year=" . $year . "&stateId=" . $state;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getBillsByYearState($year, $state_id = 'NA') {
                /**
                 * Gets a list of bills that fit the criteria
                 */
                
                $iface = "/Votes.getBillsByYearState";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&year=" . $year . "&stateId=" . $state_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getBillsByOfficialYear($can_id, $year) {
                /**
                 * Gets a list of bills that fit the criteria
                 */
                
                $iface = "/Votes.getBillsByOfficialYear";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $can_id . "&year=" . $year;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getBillsByOfficialCategory($can_id, $category_id) {
                /**
                 * Gets a list of bills that fit the criteria
                 */
                
                $iface = "/Votes.getBillsByOfficialCategory";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $can_id . "&categoryId=" . $category_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getBillsBySponsorYear($can_id, $year) {
                /**
                 * Gets a list of bills that fit the criteria
                 */
                
                $iface = "/Votes.getBillsBySponsorYear";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $can_id . "&year=" . $year;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getBillsBySponsorCategory($can_id, $category_id) {
                /**
                 * Gets a list of bills that fit the criteria
                 */
                
                $iface = "/Votes.getBillsBySponsorCategory";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $can_id . "&categoryId=" . $category_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getBillsByStateRecent($ammount = '', $state_id = 'NA') {
                /**
                 * Gets a list of bills that fit the criteria
                 */
                
                $iface = "/Votes.getBillsByStateRecent";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&stateId=" . $state_id . "&ammount=" . $ammount;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getVetoes($can_id) {
                /**
                 * Gets a list of vetoes for the provided candidate
                 */
                
                $iface = "/Votes.getVetoes";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&candidateId=" . $can_id;
                
                return $this->getXml($iface, $args);
                
        }
        
}