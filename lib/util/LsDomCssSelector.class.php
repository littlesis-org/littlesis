<?php

class LsDomCssSelector extends sfDomCssSelector 
{

  //given an id for searching will match all tags/nodes with that id, not just one
  protected function getElementsForNode($selector, $root_node)
  {
    $all_nodes = array();
    foreach ($this->tokenize_selectors($selector) as $selector)
    {
      $nodes = array($root_node);
      foreach ($this->tokenize($selector) as $token)
      {
        $combinator = $token['combinator'];
        $selector = $token['selector'];

        $token = trim($token['name']);

        $pos = strpos($token, '#');
        if (false !== $pos && preg_match('/^[A-Za-z0-9\*]*$/', substr($token, 0, $pos)))
        {
		  // Token contains an id selector
          $tagName = substr($token, 0, $pos);
          if (!$tagName)
          {
            $tagName = '*';
          }
          $id = substr($token, $pos + 1);

          // Get elements matching tag, filter them for class selector
          $founds = $this->getElementsByTagName($nodes, $tagName, $combinator);
          $nodes = array();
          foreach ($founds as $found)
          {
            if (preg_match('/\b'.$id.'\b/', $found->getAttribute('id')))
            {
              $nodes[] = $found;
            }
          }

          $nodes = $this->matchCustomSelector($nodes, $selector);

          continue; // Skip to next token
        }
        $pos = strpos($token, '.');
        if (false !== $pos && preg_match('/^[A-Za-z0-9\*]*$/', substr($token, 0, $pos)))
        {
          // Token contains a class selector
          $tagName = substr($token, 0, $pos);
          if (!$tagName)
          {
            $tagName = '*';
          }
          $className = substr($token, $pos + 1);

          // Get elements matching tag, filter them for class selector
          $founds = $this->getElementsByTagName($nodes, $tagName, $combinator);
          $nodes = array();
          foreach ($founds as $found)
          {
            if (preg_match('/\b'.$className.'\b/', $found->getAttribute('class')))
            {
              $nodes[] = $found;
            }
          }

          $nodes = $this->matchCustomSelector($nodes, $selector);

          continue; // Skip to next token
        }

        // Code to deal with attribute selectors
        if (preg_match('/^(\w+|\*)(\[.+\])$/', $token, $matches))
        {
          $tagName = $matches[1] ? $matches[1] : '*';
          preg_match_all('/
            \[
              (\w+)                 # attribute
              ([=~\|\^\$\*]?)       # modifier (optional)
              =?                    # equal (optional)
              (
                "([^"]*)"           # quoted value (optional)
                |
                ([^\]]*)            # non quoted value (optional)
              )
            \]
          /x', $matches[2], $matches, PREG_SET_ORDER);

          // Grab all of the tagName elements within current node
          $founds = $this->getElementsByTagName($nodes, $tagName, $combinator);
          $nodes = array();
          foreach ($founds as $found)
          {
            $ok = false;
            foreach ($matches as $match)
            {
              $attrName = $match[1];
              $attrOperator = $match[2];
              $attrValue = $match[4];

              switch ($attrOperator)
              {
                case '=': // Equality
                  $ok = $found->getAttribute($attrName) == $attrValue;
                  break;
                case '~': // Match one of space seperated words
                  $ok = preg_match('/\b'.preg_quote($attrValue, '/').'\b/', $found->getAttribute($attrName));
                  break;
                case '|': // Match start with value followed by optional hyphen
                  $ok = preg_match('/^'.preg_quote($attrValue, '/').'-?/', $found->getAttribute($attrName));
                  break;
                case '^': // Match starts with value
                  $ok = 0 === strpos($found->getAttribute($attrName), $attrValue);
                  break;
                case '$': // Match ends with value
                  $ok = $attrValue == substr($found->getAttribute($attrName), -strlen($attrValue));
                  break;
                case '*': // Match ends with value
                  $ok = false !== strpos($found->getAttribute($attrName), $attrValue);
                  break;
                default :
                  // Just test for existence of attribute
                  $ok = $found->hasAttribute($attrName);
              }

              if (false == $ok)
              {
                break;
              }
            }

            if ($ok)
            {
              $nodes[] = $found;
            }
          }

          continue; // Skip to next token
        }

        // If we get here, token is JUST an element (not a class or ID selector)
        $nodes = $this->getElementsByTagName($nodes, $token, $combinator);

        $nodes = $this->matchCustomSelector($nodes, $selector);
      }

      foreach ($nodes as $node)
      {
        if (!$node->getAttribute('sf_matched'))
        {
          $node->setAttribute('sf_matched', true);
          $all_nodes[] = $node;
        }
      }
    }

    return $all_nodes;
  }
  
}