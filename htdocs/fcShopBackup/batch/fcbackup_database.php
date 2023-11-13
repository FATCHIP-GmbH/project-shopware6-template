#!/usr/local/php5/bin/php-cli 

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
 
include (dirname(__FILE__)."/../core/fcbackup_class_backup.php");

$oFcBackup = new fcbackup_class_backup();
$oFcBackup->fcGenerateDatabaseBackup(false);
