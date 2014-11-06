#!/usr/bin/env php
<?php

/*
 * This file is part of the 'octris/octris-tmbundle' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * State debugger.
 *
 * @copyright   copyright (c) 2009-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */

$params = array();

require_once(__DIR__ . '/vendor/autoload.php');

$dia = new \Octris\TMDialog\Dialog();

if ($dia->load('state', $params) === false) {
    // dialog was not loaded
    fputs(STDERR, "unable to load dialog 'state.nib'.\n");

    exit(1);
}

$dia->registerAction('bindingAction', function ($model) use ($dia) {
    $secret = trim($model['thawSecret']);
    $text   = trim($model['thawState']);

    \Octris\Core\App\State::setSecret(($secret != '' ? $secret : null));

    if ($text != '') {
        try {
            $text = var_export(\Octris\Core\App\State::thaw($text), true);
        } catch(Exception $e) {
            $text = $e->getMessage();
        }
    }

    $dia->update(array('thawValue' => $text));

    return true;
});

$dia->run();
