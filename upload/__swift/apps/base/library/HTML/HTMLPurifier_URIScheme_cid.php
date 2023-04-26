<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author       Werner Garcia
 *
 * @package      SWIFT
 * @copyright    Copyright (c) 2001-2019, Trilogy
 * @license      http://www.opencart.com.vn/license
 * @link         http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Base\Library\HTML;


use HTMLPurifier_URIScheme;

class HTMLPurifier_URIScheme_cid extends HTMLPurifier_URIScheme {

    public $default_port = null;
    public $browsable = true;
    public $hierarchical = false;

    public function doValidate(&$uri, $config, $context) {
        return true;
    }

    public function validate(&$uri, $config, $context) {
        return true;
    }

}
