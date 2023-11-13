<?php

/*
 * @package FATCHIP Shop Backup
 * @author FATCHIP GmbH
 * @copyright (C) 2013, FATCHIP GmbH
 * 
 * This Software is the property of FATCHIP GmbH
 * and is protected by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be
 * prosecuted by civil and criminal law.
 */

class fcbackup_class_resticbackup
{
    public $aConfig = null;
    public $sNewLine = PHP_EOL;
    public $sResticBin = null;

    public function __construct()
    {
        // Load config.ini
        $sIniFile = dirname(__FILE__) . "/../config/config.ini";
        if (file_exists(dirname(__FILE__) . "/../config/config.ini")) {
            $this->aConfig = parse_ini_file($sIniFile, TRUE);
        } else {
            throw new ErrorException("Config ini file not found! '" . $sIniFile . "'");
        }

        // set restic binary
        $this->sResticBin = $this->aConfig['environment']['abs_htdocsdir'] . "fcShopBackup/lib/restic/restic_0.9.0_linux_amd64";
    }

    public function fcGenerateDatabaseSnapshot()
    {
        echo date("Y-m-d H:i:s") . " Start restic backup database dump" . $this->sNewLine;

		$sCom = 'cd ' . $this->aConfig['environment']['abs_htdocsdir'] . ' && ';
        $sCom .= $this->sResticBin ." -r " . $this->aConfig['restic']['database_repository'] . " --verbose backup " . $this->aConfig['environment']['abs_htdocsdir'] . "files/backup/fcDump ";
        $sCom .= "--cache-dir ".$this->aConfig['environment']['abs_htdocsdir'].".cache --password-file=" . $this->aConfig['environment']['abs_htdocsdir'] . "fcShopBackup/config/restic_repo_pwd ";
        $this->execBash($sCom);

        echo date("Y-m-d H:i:s") . " Finished restic backup database dump" . $this->sNewLine;
    }

    /**
     * Generate a restic snapshot on Strato Hidrive
     */
    public function fcGenerateFileSnapshot()
    {
        echo date("Y-m-d H:i:s") . " Start restic backup files" . $this->sNewLine;

        $sCom = $this->sResticBin ." -r " . $this->aConfig['restic']['files_repository'] . " --verbose backup " . $this->aConfig['environment']['abs_htdocsdir'] . " ";
        $sCom .= "--exclude-file=" . $this->aConfig['environment']['abs_htdocsdir'] . "fcShopBackup/config/fcbackup_exclude.lst ";
        $sCom .= "--cache-dir ".$this->aConfig['environment']['abs_htdocsdir'].".cache --password-file=" . $this->aConfig['environment']['abs_htdocsdir'] . "fcShopBackup/config/restic_repo_pwd ";
        $this->execBash($sCom);

        echo date("Y-m-d H:i:s") . " Finished restic backup files" . $this->sNewLine;
    }

    public function fcCleanupRepository($sRepoType = 'files_repository')
    {
        echo date("Y-m-d H:i:s") . " Started restic repository cleanup '" . $sRepoType . "'" . $this->sNewLine;

        $sRepo = false;
        if ($sRepoType == 'files_repository'){
            $sRepo = $this->aConfig['restic']['files_repository'];
        } elseif ($sRepoType == 'database_repository') {
            $sRepo = $this->aConfig['restic']['database_repository'];
        }

        if($sRepo) {
            // first clear all locks
            $sClean = $this->sResticBin . " -r " . $sRepo . " unlock ";
            $sClean .= "--cache-dir ".$this->aConfig['environment']['abs_htdocsdir'].".cache --password-file=" . $this->aConfig['environment']['abs_htdocsdir'] . "fcShopBackup/config/restic_repo_pwd ";
            $this->execBash($sClean);

            // delete old snapshots and prune
            $sCom = $this->sResticBin . " -r " . $sRepo . " forget --keep-daily 31 --keep-monthly 12 --keep-yearly 100 --prune ";
            $sCom .= "--cache-dir ".$this->aConfig['environment']['abs_htdocsdir'].".cache --password-file=" . $this->aConfig['environment']['abs_htdocsdir'] . "fcShopBackup/config/restic_repo_pwd ";
            $this->execBash($sCom);
        } else {
            echo date("Y-m-d H:i:s") . " [ERROR] Repository not specified" . $this->sNewLine;
        }

        echo date("Y-m-d H:i:s") . " Finished restic repository cleanup '" . $sRepoType . "'" . $this->sNewLine;
    }

    /**
     * Executes a shell command.
     *
     * @param $sCommand
     *
     * @return mixed
     *
     * @throws ErrorException
     */
    protected function execBash($sCommand)
    {
        exec($sCommand, $aOutput, $iStatus);

        if ($iStatus !== 0) {
            if (is_array($aOutput)) {
                foreach ($aOutput AS $sOutput) {
                    echo ($sOutput . PHP_EOL);
                }
            }
            throw new ErrorException("Error executing bash command '" . $sCommand . "'");
        }

        return $aOutput;
    }
}
