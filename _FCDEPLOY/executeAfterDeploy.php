<?php
/**
 * FATCHIP after file deployment actions.
 *
 * @author FATCHIP GmbH <kontakt@fatchip.de>
 */

// Include execution base
include_once 'autoload.inc.php';

// Define vars
$sConfigDir = dirname(__FILE__) . "/../_FCCONFIG";
$sDocRoot = dirname(__FILE__) . "/../htdocs";
$sTmpDir = $sDocRoot . "/cache";
$oFcDeploy = new \FatDeploy\Deploy();

// Copy config files of given environment from _FCCONFIG dir
if (is_dir($sConfigDir)) {
    $oFcDeploy->updateFcConfigFilesNoSync($sConfigDir, $sDocRoot, $aParams['environment']);
    $oFcDeploy->deleteFcConfigDir($sConfigDir);
}

// set file rights
$aChangeMod = array(
    dirname(__FILE__) . '/../htdocs/.htpasswd' => 444,
    dirname(__FILE__) . '/../htdocs/.htaccess' => 444,
);
foreach ($aChangeMod as $sFile => $iMod) {
    $oFcDeploy->chmodFile($sFile, $iMod);
}

// Clean up cache dir
$oFcDeploy->deleteCache($sTmpDir);

// minimize css
$aCssFiles = array(
	$sDocRoot . "/css/main.css",
	//$sDocRoot . "www/css/bootstrap.css"
);
foreach ($aCssFiles AS $sCssFile){
	if (file_exists($sCssFile)) {
		\FatDeploy\Log::log("Minimizing: " . $sCssFile);
		$sCss = file_get_contents($sCssFile);
		$minimizedCode = CssMin::minify($sCss);
		if ($minimizedCode && $minimizedCode != "") {
			file_put_contents($sCssFile, $minimizedCode);
		} else {
			throw new ErrorException("Warning: Failed minimizing " .  $sCssFile, 0);
		}
	} else {
		\FatDeploy\Log::log($sCssFile . 'not found');
	}
}

