<?php

namespace FatDeploy;

/**
 * Deployment Functions.
 *
 * @author FATCHIP GmbH <kontakt@fatchip.de>
 */
class Deploy
{
    public function chmodFile($sFile, $iRights)
    {
        if (file_exists($sFile)) {
            $sCommand = "chmod " . $iRights . " " . $sFile;
            $this->execBash($sCommand);
        }
    }

    public function deleteFcConfigDir($sConfigDir)
    {
        $sCommand2 = "rm -r " . $sConfigDir;
        $this->execBash($sCommand2);
    }

    public function deleteDir($sDir)
    {
        $sCommand1 = 'rm -vrf '. $sDir.'/* 2>&1';
        $this->execBash($sCommand1);
    }

    public function deleteCache($sTmpDir)
    {
        $sCommand1 = 'rm -vf '. $sTmpDir .'/*.html 2>&1';
        $this->execBash($sCommand1);
        $sCommand2 = 'rm -vf '. $sTmpDir .'/*.json 2>&1';
        $this->execBash($sCommand2);
    }

    public function execBash($sCommand)
    {
        Log::log($sCommand);
        exec($sCommand, $aOutput, $iStatus);
        if (is_array($aOutput)) {
            foreach ($aOutput AS $sOutput) {
                Log::log($sOutput);
            }
        }
        if ($iStatus !== 0) {
            throw new \ErrorException("Error executing '" . $sCommand . "'");
        }

        return $aOutput;
    }

    public function getOxidCssFileNames($sDocRoot){
        $aFiles = array();
        $aDirs = array(
            '/out/*/src/',
            '/out/*/src/css/',
            '/out/*/src/css/libs/',
            '/modules/oe/oepaypal/out/src/css/',
        );
        foreach ($aDirs AS $sDir){
            foreach(glob($sDocRoot.$sDir.'*.css') as $cssFile) {
                if (!preg_match("/min\.css/i", basename($cssFile)) ){
                    $aFiles[] = $cssFile;
                }
            }
        }
        return $aFiles;
    }

    public function minimizeCssFile($sCssFile)
    {
        if (file_exists($sCssFile)) {
            Log::log("Minimizing: " . $sCssFile);
            $sCss = file_get_contents($sCssFile);
            $minimizedCode = \CssMin::minify($sCss);
            if ($minimizedCode && $minimizedCode != "") {
                file_put_contents($sCssFile, $minimizedCode);
            } else {
                throw new \ErrorException("Warning: Failed minimizing " .  $sCssFile . "(Code: " . $minimizedCode . " )", 0);
            }
        } else {
            throw new \ErrorException("Error minimizing: " . $sCssFile . " not found.", 0);
        }
    }

    public function updateFcConfigFiles($sConfigDir, $sDocRoot, $sEnvironment)
    {
        if (is_dir($sConfigDir . "/" . $sEnvironment) && is_dir($sConfigDir . "/" . $sEnvironment . "/files")) {
            $sCommand1 = "rsync --recursive --verbose -K " . $sConfigDir . "/" . $sEnvironment . "/files/* " . $sDocRoot . "/";
            $this->execBash($sCommand1);
        } else {
            Log::log("No changed _FCCONFIG files for " . $sEnvironment);
        }
    }

    public function updateFcConfigFilesNoSync($sConfigDir, $sDocRoot, $sEnvironment)
    {
        if (is_dir($sConfigDir . "/" . $sEnvironment) && is_dir($sConfigDir . "/" . $sEnvironment . "/files")) {
            $sCommand1 = "cp --recursive --verbose -R " . $sConfigDir . "/" . $sEnvironment . "/files/. " . $sDocRoot . "/";
            $this->execBash($sCommand1);
        } else {
            Log::log("No changed _FCCONFIG files for " . $sEnvironment);
        }
    }
}
