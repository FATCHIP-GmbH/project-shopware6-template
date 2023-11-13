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

class fcbackup_class_backup
{
    /*
     * dirname for the export files
     */

    public $sFcExportDir = "";

    /*
     * file name prefix for todays backup
     */
    protected $_sFileNamePrefix = "";

    /*
     * project name
     */
    protected $_sProjectName = "";

    /*
     * port for mysql access
     */
    protected $_dbPort = "";

    /*
     * socket for mysql access
     */
    protected $_dbSocket = "";

    /*
     * new line seperator for debug output
     */
    protected $_sNewLine = PHP_EOL;
    protected $_blDebug = true;
    
    protected $sMysqldumpInterpreter = "mysqldump";

    protected $shopRoot = '/var/www/XXX/htdocs/prod';

    /**
     * class constructor
     */
    public function __construct()
    {
        if (defined('STDIN') || PHP_SAPI === "cgi") {
            $this->_sNewLine = " \n";
        }

        $env = parse_ini_file($this->shopRoot . '/htdocs/.env');
        echo "DB URL from env: " . $env['DATABASE_URL'];
        $parsedUrl = parse_url($env['DATABASE_URL']);

	    $this->dbHost = $parsedUrl['host'] . ':' . $parsedUrl['port'];
	    $this->dbUser = $parsedUrl['user'];
	    $this->dbPwd = $parsedUrl['pass'];
	    $this->dbName = substr($parsedUrl['path'], 1);
	    $this->dbType = 'mysql';

        // handle hostname, port and socket
        $aConfigHost = explode(":", $this->dbHost);
        $this->dbHost = $aConfigHost[0];
        if (isset($aConfigHost[1]) && is_numeric($aConfigHost[1])) {
            $this->_dbPort = $aConfigHost[1];
        } else if (isset($aConfigHost[1]) && !is_numeric($aConfigHost[1])) {
            $this->_dbSocket = $aConfigHost[1];
        }

        // remove http:// from shopname
        $this->_sProjectName = 'shop.schmuckhalbzeug.de';

        // set export dir
        $this->sFcExportDir = $this->getShopBasePath() . "/htdocs/files/backup/";

        // generate file prefix
        $this->_sFileNamePrefix = date("ymd") . "_";
    }

    /**
     * @return string installation path on server
     */
    public function getShopBasePath()
    {
        return $this->shopRoot;
    }

    /**
     * read the fcbackup_db_exclude.lst file and return the content
     * @return array
     */
    protected function _getConfiguredTableExcludes()
    {
        $aTables = array();

        $sFilePath = $this->getShopBasePath() . "fcShopBackup/config/fcbackup_db_exclude.lst";
        if (file_exists($sFilePath)) {
            $oFileHandle = fopen($sFilePath, 'r');
            if ($oFileHandle) {
                while (!feof($oFileHandle)) {
                    $sTable = trim(fgets($oFileHandle));
                    if ($sTable) {
                        $aTables[] = $sTable;
                    }
                }
            }
        }
        return $aTables;
    }

    public function fcGenerateDatabaseBackup($blUsePrefix = false)
    {
        $sMysqldumpInterpreter = $this->sMysqldumpInterpreter;
        $aConfiguredExcludes = $this->_getConfiguredTableExcludes();
        $sDumpDir = $this->sFcExportDir . 'fcDump';
        if(!is_dir($sDumpDir)){
            mkdir($sDumpDir);
        }



        // Getting table names from database excluding views
        if ($this->_blDebug)
            echo "Getting table names from database..." . PHP_EOL;

        $sCommand1  = 'mysql '.$this->dbName.' -h'.$this->dbHost.' -u'.$this->dbUser.' -p'.$this->dbPwd;
        if ($this->_dbPort != "") {
            $sCommand1 .= ' --port=' . $this->_dbPort;
        }
        if ($this->_dbSocket != "") {
            $sCommand1 .= ' --socket=' . $this->_dbSocket;
        }
        $sCommand1 .= ' -e \'show tables where tables_in_'.$this->dbName.' not like "oxv\_%" AND tables_in_'.$this->dbName.' not like "%\_tmp"';
        foreach ($aConfiguredExcludes as $sTable) {
            $sCommand1 .= 'AND tables_in_'.$this->dbName.' not like "' . $sTable . '"';
        }
        $sCommand1 .= '\' | grep -v Tables_in';

        if ($this->_blDebug)
            echo $sCommand1 . PHP_EOL;

        $aTableNames = $this->execBash($sCommand1);

        // dump database tables one by one
        if ($this->_blDebug)
            echo "Creating dump files..." . PHP_EOL;


        echo "dumping to " . $sDumpDir . PHP_EOL;


        foreach ($aTableNames AS $sTableName){
            $sCommand2 = $sMysqldumpInterpreter . " --skip-lock-tables --no-tablespaces -h".$this->dbHost.' -u'.$this->dbUser.' -p'.$this->dbPwd.' '.$this->dbName.' '. $sTableName . " > " . $sDumpDir . "/";
            if ($blUsePrefix) {
                $sCommand2 .= $this->_sFileNamePrefix;
            }
            $sCommand2 .= $this->_sProjectName . "." . $sTableName . ".sql";

            if ($this->_blDebug)
                echo $sCommand2 . PHP_EOL;

            $this->execBash($sCommand2);

            if ($this->_blDebug)
                echo "Dumped " . $sTableName . PHP_EOL;
        }

        // clean old files
        if ($this->_blDebug)
            echo "clean up old backup" . $this->_sNewLine;

        $this->execBash("rm -f {$this->sFcExportDir}*sql.tar.gz");
    }

    /**
     * packs the SQL dump to a tar file
     */
    public function fcTarDatabaseBackup()
    {
        // compress files to tar archive
        if ($this->_blDebug)
            echo "compress files" . $this->_sNewLine;

        $this->execBash("tar cfz {$this->sFcExportDir}{$this->_sFileNamePrefix}{$this->_sProjectName}_fcShopBackup.sql.tar.gz {$this->sFcExportDir}/fcDump/*");

        // clean up dump SQL files
        if ($this->_blDebug)
            echo "clean up" . $this->_sNewLine;

        $this->execBash("rm -rf {$this->sFcExportDir}/fcDump");

        if ($this->_blDebug)
            echo $this->sShopURL . "/export/fcShopBackup/{$this->_sFileNamePrefix}{$this->_sProjectName}_fcShopBackup.sql.tar.gz" . $this->_sNewLine;
    }

    public function removeInserts($aTableNames, $blBlacklist = true, $blStripTableStructure = false)
    {
        $aFiles = glob($this->sFcExportDir . '/*dump.sql');

        $aExcludes = array('.', '..');
        if (!in_array($aFiles, $aExcludes)) {
            array_multisort(
                    array_map('filemtime', $aFiles), SORT_NUMERIC, SORT_DESC, $aFiles
            );
        }

        $sFilePath = false;
        if (is_array($aFiles) && isset($aFiles[0])) {
            foreach ($aFiles as $key => $value) {
                if (preg_match("#^.*dump\.sql$#i", $value)) {
                    $sFilePath = $value;
                    break;
                }
            }
        }

        if ($sFilePath !== false && is_file($sFilePath) && file_exists($sFilePath)) {
            $oFh = fopen($sFilePath, 'r');
            if ($oFh) {
                $sFilePathNew = str_replace('dump.sql', 'dump_mini.sql', $sFilePath);
                $oFhNew = fopen($sFilePathNew, 'w');
                $sReadLine = '';
                $blLock = false;
                while (!feof($oFh)) {
                    $sReadLine = fgets($oFh);

                    if ($blStripTableStructure === true) {
                        $sIndicator = 'DROP TABLE IF EXISTS `';
                        $sIndicatorRegex = "#^DROP TABLE IF EXISTS `(.*)`;$#";
                    } else {
                        $sIndicator = 'LOCK TABLES `';
                        $sIndicatorRegex = "#^LOCK TABLES `(.*)` WRITE;$#";
                    }

                    if (stripos($sReadLine, $sIndicator) !== false) {
                        if ($blBlacklist === false) {
                            $blLock = true;
                        }
                        preg_match($sIndicatorRegex, $sReadLine, $aOutput);
                        if ($aOutput && is_array($aOutput) && count($aOutput) == 2 && array_search($aOutput[1], $aTableNames) !== false) {
                            if ($blBlacklist === true) {
                                $blLock = true;
                            } else {
                                $blLock = false;
                            }
                        }
                    }

                    if ($blLock === false) {
                        fwrite($oFhNew, $sReadLine);
                    }

                    if (stripos($sReadLine, 'UNLOCK TABLES;') !== false) {
                        $blLock = false;
                    }
                }

                fclose($oFhNew);
                fclose($oFh);
            }
        }
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
            throw new ErrorException("Error executing '" . $sCommand . "'");
        }

        return $aOutput;
    }

}
