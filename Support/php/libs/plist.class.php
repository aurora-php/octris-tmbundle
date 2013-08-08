<?php

/**
 * Plist parser.
 *
 * @octdoc      libs/plist
 * @copyright   copyright © 2009-2013 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class plist 
/**/
{
    /**
     * Constructor.
     *
     * @octdoc  plist/__construct
     */
    public function __construct()
    /**/
    {
    }

    /**
     * Main parser.
     *
     * @octdoc  plist/parsePlist
     * @param   DOMNode             $node           Node to parse.
     * @return  array                               Data of parsed node.
     */
    protected function parsePlist(DOMNode $node)
    /**/
    {
        $type = $node->nodeName;
        $name = 'parse' . ucfirst($type);

        switch ($type) {
            case 'integer':
                /** FALL THRU **/
            case 'string':
                /** FALL THRU **/
            case 'data':
                /** FALL THRU **/
            case 'date':
                $return = $node->textContent;
                break;
            case 'true':
                $return = true;
                break;
            case 'false':
                $return = false;
                break;
            default:
                // complex parsers
                if ($name != 'parse' && method_exists($this, $name)) {
                    $return = $this->{$name}($node);
                } else {
                    $return = NULL;
                }
        }
        
        return $return;
    }

    /**
     * Parse plist dictionary.
     *
     * @octdoc  plist/parseDict
     * @param   DOMNode             $node           Node to parse.
     * @return  array                               Data of parsed node.
     */
    protected function parseDict(DOMNode $node)
    /**/
    {
        $dict = array();
        
        // for each child of this node
        for ($child = $node->firstChild; $child != null; $child = $child->nextSibling) {
            if ($child->nodeName == 'key') {
                $key = $child->textContent;

                $vnode = $child->nextSibling;

                // skip text nodes
                while ($vnode->nodeType == XML_TEXT_NODE) $vnode = $vnode->nextSibling;
            
                // recursively parse the children
                $value = $this->parse($vnode);

                $dict[$key] = $value;
            }
        }

        return $dict;
    }

    /**
     * Parse plist array.
     *
     * @octdoc  plist/parseDict
     * @param   DOMNode             $node           Node to parse.
     * @return  array                               Data of parsed node.
     */
    protected function parseArray(DOMNode $node)
    /**/
    {
        $array = array();

        for ($child = $node->firstChild; $child != null; $child = $child->nextSibling) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                array_push($array, $this->parse($child));
            }
        }

        return $array;
    }
    
    /**
     * Parse plist xml.
     *
     * @octdoc  plist/process
     * @param   string              $xml                XML to parse.
     * @return  array                                   Plist data as php array.
     */
    public function parse($xml)
    /**/
    {
        $plist = new DOMDocument();
        $plist->loadXML($xml);
        
        $root = $plist->documentElement->firstChild;

        while ($root->nodeName == '#text') $root = $root->nextSibling;

        return $this->parsePlist($root);
    }
}
