<?php
/**
 * BugFree is free software under the terms of the FreeBSD License.
 *
 * admin user list.
 *
 * @link        http://www.bugfree.org.cn
 * @package     BugFree
 */
/* Init BugFree system. */
require_once("../Include/Init.inc.php");

baseJudgeAdminUserLogin();

if($_REQUEST['reset'])
{
   $_SESSION['SearchUser']='';
}


/* Get pagination */
$DBName = 'MyDB';
global $$DBName;

$Where = "(1)";
if(isset($_GET['SearchUser']))
{
    $SSearchUser = sysStripSlash(trim($_GET['SearchUser']));
    $_SESSION['SearchUser'] =  $SSearchUser;
}

if($SSearchUser != '')
{
    $SSearchUser = my_escape_string(my_escape_string($SSearchUser));
    $Where .= " AND ( BINARY UserName like '%{$SSearchUser}%' ";
    $Where .= " OR BINARY RealNamelike '%{$SSearchUser}%' ";
    $Where .= " OR BINARY Email like '%{$SSearchUser}%' )";
}
else
{
    if($_SESSION['SearchUser'] != '')
    {
       $SSearchUser =  $_SESSION['SearchUser'];
       $SSearchUser = my_escape_string(my_escape_string($SSearchUser));
       $Where .= " AND ( BINARY UserName like '%{$SSearchUser}%' ";
       $Where .= " OR BINARY RealName like '%{$SSearchUser}%' ";
       $Where .= " OR BINARY Email like '%{$SSearchUser}%' )";
    }
}

{
    //$PageWhere = "WHERE IsDroped = '0' ORDER BY UserID DESC";
    $PageWhere = "WHERE {$Where} ORDER BY UserID DESC";
    $OrderBy = "UserID DESC";
}

$OrderBy = "UserID DESC";
$Pagination = new Page('TestUser', '', '', '', $PageWhere, '?SearchUser='.sysAddSlash($_SESSION['SearchUser']), $$DBName);
$LimitNum = $Pagination->LimitNum();

/* Get user list */
$UserList = testGetAllUserList($Where, $OrderBy, $LimitNum);
$UserNameList = testGetOneDimUserList();


foreach($UserList as $UserName => $UserInfo)
{
    $SUserName = my_escape_string(my_escape_string($UserInfo['UserName']));
    $GroupACL = dbGetList('TestGroup', '', "GroupUser LIKE '%," . $SUserName . ",%'");
    $UserGroupList = array();
    foreach($GroupACL as $Key => $GroupInfo)
    {
        $UserGroupList[$GroupInfo['GroupID']] = $GroupInfo['GroupName'];
    }
    $UserList[$UserName]['UserGroupListHTML'] = htmlSelect($UserGroupList, 'UserGroupList','', '', 'class="FullSelect"');
    $UserList[$UserName]['AuthModeName'] = $_LANG['AuthMode'][$UserInfo['AuthMode']];
}

/* Assign */
$TPL->assign('PaginationHtml', $Pagination->show('right', 'margin-right:20px'));
$TPL->assign('UserList', $UserList);
$TPL->assign('UserNameList', $UserNameList);

/* Display the template file. */


$TPL->assign('NavActiveUser', ' class="Active"');
$TPL->assign('SearchUser', $_SESSION['SearchUser']);

$TPL->display('Admin/UserList.tpl');
?>
