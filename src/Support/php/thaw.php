#!/usr/bin/env php
<?php

/**
 * Thaw octris state for debugging purpose.
 *
 * @octdoc      php/thaw.php
 * @copyright   copyright © 2009-2013 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
/**/

$params = array();

require_once('libs/tmdialog.class.php');

$dia = new tmdialog();

if ($dia->load('thaw', $params) === false) {
    // dialog was not loaded
    fputs(STDERR, "unable to load dialog 'thaw.nib'.\n");
    
    exit(1);
}

$dia->run();
