<?php

require_once("config.php");

/**
 * VoteSmart API interfacing class
 * 
 * This class can be initialized with params or used 
 * repeatedly by directly calling query()
 * 
 * Copyright 2008 Project Vote Smart
 * Distributed under the BSD License
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 * Special thanks to Adam Friedman for the idea and code
 * contribution for the slimmed down version of this lib.
 * 
 */
class VoteSmart {
        
        protected $iface;          // Interface(URL) used to gain the data
        protected $xml;            // Raw XML
        protected $xmlObj;         // SimpleXML object
        
        /**
         * function __construct
         * 
         * Initialize object(optional)
         * 
         * @param string $method optional 'CandidateBio.getBio'
         * @param array $args optional Array('candidateId' => '54321')
         */
        public function __construct($method = null, $args = null) {
                
                if ($method && $args) {
                        
                        $this->query($method, $args);
                        
                }
                
        }
        
        /**
         * function getXml
         * 
         * Return raw XML string
         * 
         * @return string
         */
        public function getXml() {
                
                return $this->xml;
                
        }
        
        /**
         * function getXmlObj
         * 
         * Return SimpleXML object
         * 
         * @return object SimpleXMLElement
         */
        public function getXmlObj() {
                
                return $this->xmlObj;
                
        }
        
        /**
         * function getIface
         * 
         * Return string of URL queried
         * 
         * @return string
         */
        public function getIface() {
                
                return $this->iface;
                
        }
        
        /**
         * function query
         * 
         * Query API backend and return SimpleXML object.  This
         * function can be reused repeatedly
         * 
         * @param string $method CandidateBio.getBio'
         * @param array $args Array('candidateId' => '54321')
         * @return object SimpleXMLElement
         */
        public function query($method, $args = Array()) {
                
                $terms = "";
                
                if(!empty($args)) {
				
			foreach($args as $n => $v) {
				
				$terms .= '&' . $n . '=' . $v;
				 
			}
		}
		
		$this->iface = _APISERVER_ . "/" . $method . "?key=" . _KEY_ . "&o=" . _OUTPUT_  . $terms;
		
                if (!$this->xml = file_get_contents($this->iface)) {
                		
                        return false;
                		
                } else {
                		
                        // Let's use the SimpleXML to drop the whole XML
                        // output into an object we can later interact with easilly
                        $this->xmlObj = new SimpleXMLElement($this->xml, LIBXML_NOCDATA);
                        
                        return $this->xmlObj;
                		
                }
                
        }
	
}
