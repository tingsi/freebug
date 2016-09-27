<?php
/**
 * BugFree is free software under the terms of the FreeBSD License.
 *
 * install.
 *
 * @link        http://www.bugfree.org.cn
 * @package     BugFree
 */
/* Init BugFree system. */
@set_time_limit(0);
@ini_set('memory_limit', -1);

require_once('Include/Init.inc.php');
require_once('Schema.php');
$_CFG['DebugMode'] = true;

@session_destroy();
@setcookie("BFUserPWD", '', time()-1209600,BF_COOKIE_PATH);

if(!function_exists('mysqli_connect'))
{
    $ErrorMsg[] = $_LANG["InstallErrorNoMysqlModule"];
    $TPL->assign("ErrorMsg", join("<br />\n", $ErrorMsg));
    $TPL->assign("NoBack", true);
    $TPL->display('Install.tpl');
    exit;
}

sysMakeDBExists($_CFG['DB']['Database']);


$ErrorMsg = array();

if(!isset($_CFG['DB']['TablePrefix']))$_CFG['DB']['TablePrefix'] = '';
$TablePrefix = $_CFG['DB']['TablePrefix'];

$BugFree1TableList = array(
"BugFile",
"BugGroup",
"BugHistory",
"BugInfo",
"BugModule",
"BugProject",
"BugQuery",
"BugUser",
);

$BugFree2TableList = array(
$TablePrefix."BugInfo",
$TablePrefix."TestAction",
$TablePrefix."TestFile",
$TablePrefix."TestHistory",
$TablePrefix."TestModule",
$TablePrefix."TestProject",
$TablePrefix."TestUser",
$TablePrefix."TestUserLog",
$TablePrefix."TestUserQuery",
$TablePrefix."TestOptions",
);

$CurrentDBVersion =  BF_DB_VERSION;



$BugFree2Exists = false;
$TPL->assign('BugFree2Exists', $BugFree2Exists);


if($_GET['Step'] == 2)
    installNewBugFree();

$TPL->assign('Step', $_GET['Step']);
$TPL->assign('BF_DB_VERSION', BF_DB_VERSION);
$TPL->assign('CurrentDBVersion', $CurrentDBVersion);
$TPL->assign('ErrorMsg', $ErrorMsg);
$TPL->assign('InstallOrUpgrade', "InstallnewBugFree");
$TPL->clear_compiled_tpl();
$TPL->display('Install.tpl');



function dbAddColumn($TableName, $FieldName, $FieldDesc)
{
    global $MyDB;
    $FieldExists = false;
    $FieldInfo = dbGetFieldInfo($TableName, $FieldName);
    if($FieldInfo['Field']) $FieldExists = true;
    if(!$FieldExists)
    {
        $MyDB->query("ALTER TABLE `{$TableName}` ADD {$FieldName} {$FieldDesc};");
    }
}

function installNewBugFree()
{
    global $TablePrefix, $ErrorMsg, $BugFreeInstallSQL, $_LANG;

    sysExecuteSql($BugFreeInstallSQL, $TablePrefix);

    if(!empty($ErrorMsg)) return;

    $Password = baseEncryptUserPWD('123456');
    $UserID = dbInsertRow('TestUser', "'admin','admin','{$Password}', '', now(), 'admin', now(), '0'"
        , "UserName, RealName, UserPassword, Email, AddDate, LastEditedBy, LastDate, IsDroped");

    $ProjectID = dbInsertRow('TestProject', "'Sample','0', '','', now(), 'admin', now()"
        , "ProjectName, DisplayOrder, ProjectDoc, ProjectPlan, AddDate, LastEditedBy, LastDate");

    $GroupACL[$ProjectID] = 'All';
    $GroupACL = serialize($GroupACL);
    $GroupID = dbInsertRow('TestUserGroup', "'Sample',',admin,','{$GroupACL}', now(), 'admin', now()"
        , "GroupName, GroupUser, GroupACL, AddDate, LastEditedBy, LastDate");

    $ValueSql .= "'{$ProjectID}','Sample','0','/',";
    $ValueSql .= "'{$_LANG["SampleBugInfo"]["BugTitle"]}','4',4,'Others','{$BugOS}',";
    $ValueSql .= "'{$BugBrowser}','{$BugMachine}','Other','{$_LANG["SampleBugInfo"]["ReproSteps"]}','Active',";
    $ValueSql .= "'{$LinkID}','{$DuplicateID}',NULL,";
    $ValueSql .= "'{$MailTo}','admin',now(),'N/A','admin',now(),'admin',now(),',admin,',";
    $ValueSql .= "'{$BugKeyWord}'";
    $BugID = dbInsertRow('BugInfo',$ValueSql,"ProjectID,ProjectName,ModuleID,ModulePath,BugTitle,BugSeverity,BugPriority,BugType,BugOS,BugBrowser,BugMachine,HowFound,ReproSteps,BugStatus,LinkID,DuplicateID,ResultID,MailTo,OpenedBy,OpenedDate,OpenedBuild,AssignedTo,AssignedDate,LastEditedBy,LastEditedDate,ModifiedBy,BugKeyword");

    $GroupID = dbInsertRow('TestGroup', "'{$_LANG["AllUserGroupName"]}','','','',now(),'',now()"
        ,'GroupName,GroupManagers,GroupUser,AddedBy,AddDate,LastEditedBy,LastDate');
    sysUpdateOptions('dbVersion', BF_DB_VERSION);
}

function sysUpdateOptions($OptionName, $OptionValue)
{
    $OptionInfo = dbGetRow('TestOptions', '', "OptionName = '{$OptionName}'");
    if(empty($OptionInfo))
    {
        dbInsertRow('TestOptions', "'{$OptionName}', '{$OptionValue}'", "OptionName,OptionValue");
    }
    else
    {
        dbUpdateRow('TestOptions', 'OptionValue', "'{$OptionValue}'", "OptionName = '{$OptionName}'");
    }
}

function sysExecuteSql($SQL, $TablePrefix = '')
{
    global $MyDB;
    global $ErrorMsg;

    // Read the table structure definition sql.
    $sql = addslashes($SQL);
    $sql = trim($sql);
    $sql = preg_replace("/#[^\n]*\n/", "", $sql);
    $sql = preg_replace("/--[^\n]*\n/", "", $sql);
    $sql = preg_replace("/CREATE TABLE `([a-z]{1,})`/i", "CREATE TABLE `{$TablePrefix}\\1`", $sql);
    $sql = preg_replace("/ALTER TABLE `([a-z]{1,})`/i", "ALTER TABLE `{$TablePrefix}\\1`", $sql);
    $sql = preg_replace("/UPDATE `([a-z]{1,})`/i", "UPDATE `{$TablePrefix}\\1`", $sql);
    $buffer = array();
    $ret    = array();
    $in_string = false;
    for($i=0; $i<strlen($sql)-1; $i++)
    {
        if($sql[$i] == ";" && !$in_string)
        {
            $ret[] = substr($sql, 0, $i);
            $sql = substr($sql, $i + 1);
            $i = 0;
        }

        if($in_string && ($sql[$i] == $in_string) && $buffer[0] != "\\")
        {
            $in_string = false;
        }
        elseif(!$in_string && ($sql[$i] == "\"" || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\"))
        {
            $in_string = $sql[$i];
        }
        if(isset($buffer[1]))
        {
            $buffer[0] = $buffer[1];
        }
        $buffer[1] = $sql[$i];
    }
    if(!empty($sql))
    {
        $ret[] = $sql;
    }

    // Excute the sql.
    $DBErrorMsg = array();
    for ($i=0; $i<count($ret); $i++)
    {
        $ret[$i] = stripslashes(trim($ret[$i]));
        if(!empty($ret[$i]) && $ret[$i] != "#")
        {
            $MyDB->query($ret[$i]) or $DBErrorMsg[] = $MyDB->error;
            if(count($DBErrorMsg)>0)
            {
                $ErrorMsg = $DBErrorMsg;
                return;
            }
        }
    }
}

