<?php
/**
 * FATCHIP deployment actions execution base.
 * Will always be executed.
 *
 * @author FATCHIP GmbH <kontakt@fatchip.de>
 */

// Get parameters from call
$aParams = array();
if ($argv) {
    unset($argv[0]);
    foreach ($argv as $sParamValuePair) {
        $aParam = explode('=', $sParamValuePair);
        $aParams[$aParam[0]] = $aParam[1];
    }
}

// Validate needed parameters
if (!isset($aParams['environment'])) {
    throw new \ErrorException("Script call parameter 'environment' missing!", 1);
}

// include library
include_once 'lib/Log.php';
include_once 'lib/Cssmin.php';
include_once 'lib/Deploy.php';

