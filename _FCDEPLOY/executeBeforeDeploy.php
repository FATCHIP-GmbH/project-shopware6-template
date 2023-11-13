<?php
/**
 * FATCHIP before file deployment actions.
 *
 * @author FATCHIP GmbH <kontakt@fatchip.de>
 */

// Include execution base
include_once 'autoload.inc.php';

$oFcDeploy = new \FatDeploy\Deploy();

$aChangeMod = array(
    dirname(__FILE__) . '/../htdocs/.htaccess' => 644,
    dirname(__FILE__) . '/../htdocs/.htpasswd' => 644,
);
foreach ($aChangeMod as $sFile => $iMod) {
    $oFcDeploy->chmodFile($sFile, $iMod);
}

