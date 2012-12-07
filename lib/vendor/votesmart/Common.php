<?php

/**
 * Common class contains general functions that the rest
 * of the scripts may need.
 * 
 * Copyright 2007 Project Vote Smart
 * Distributed under the BSD License
 * 
 * http://www.opensource.org/licenses/bsd-license.php
 * 
 */

class Common {
        
        protected function getXml($iface, $args) {

                //added by LittleSis to limit response time
                $ctx = stream_context_create(array(
                    'http' => array(
                        'timeout' => 5
                        )
                    )
                ); 
                
                if (!$xml = file_get_contents(_APISERVER_ . $iface . $args, 0, $ctx)) {
                        
                        return false;
                        
                } else {
                        
                        // Let's use the SimpleXML to drop the whole XML
                        // output into an object we can later interact with easilly
                        $xml_object = new SimpleXMLElement($xml, LIBXML_NOCDATA);
                        
                        return $xml_object;
                        
                }
                
        }
        
}