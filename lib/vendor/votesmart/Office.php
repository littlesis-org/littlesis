<?php

/**
 * Office class contains all the methods necessary 
 * 
 * Copyright 2007 Project Vote Smart
 * Distributed under the BSD License
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 */

require_once("config.php");
require_once("Common.php");

class Office extends Common {
        
        public function getTypes() {
                /**
                 * Gets a list of office types
                 */
                
                $iface = "/Office.getTypes";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getBranches() {
                /**
                 * Returns branches of government and their respective IDs
                 */
                
                $iface = "/Office.getBranches";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getLevels() {
                /**
                 * Returns levels of government and their respective IDs
                 */
                
                $iface = "/Office.getLevels";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getOfficesByType($type_id) {
                /**
                 * Returns a list of offices by office type
                 */
                
                $iface = "/Office.getOfficesByType";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&typeId=" . $type_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getOfficesByLevel($level_id) {
                /**
                 * Returns a list of offices by level of government
                 */
                
                $iface = "/Office.getOfficesByLevel";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&levelId=" . $level_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getOfficesByTypeLevel($type_id, $level_id) {
                /**
                 * Returns a list of offices by office type and level of government
                 */
                
                $iface = "/Office.getOfficesByType";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&typeId=" . $type_id . "&levelId=" . $leve_id;
                
                return $this->getXml($iface, $args);
                
        }
        
        public function getOfficesByBranchLevel($branch_id, $level_id) {
                /**
                 * Returns a list of offices by branch and level of government
                 */
                
                $iface = "/Office.getOfficesByBranchLevel";
                $args = "?key=" . _KEY_ . "&o=" . _OUTPUT_ . "&branchId=" . $branch_id . "&levelId=" . $level_id;
                
                return $this->getXml($iface, $args);
                
        }
        
}