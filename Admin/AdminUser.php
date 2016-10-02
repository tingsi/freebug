<?php
/**
 * BugFree is free software under the terms of the FreeBSD License.
 *
 * admin edit user.
 *
 * @link        http://www.bugfree.org.cn
 * @package     BugFree
 */
/* Init BugFree system. */
require_once("../Include/Init.inc.php");

baseJudgeAdminUserLogin();
sysXajaxRegister("xAdminAddUser,xAdminEditUser");

$ActionType = $_GET['ActionType'];

if($ActionType == 'EditUser')
{
    $UserInfo = dbGetRow('TestUser', '', "UserID = '{$_GET[UserID]}'");
    $GroupACL = dbGetList('TestGroup', '', "GroupUser LIKE '%," . my_escape_string(my_escape_string($UserInfo['UserName'])) . ",%'");
    $UserGroupList = array();
    foreach($GroupACL as $Key => $GroupInfo)
    {
        $UserGroupList[$GroupInfo['GroupID']] = $GroupInfo['GroupName'];
    }

    if(isset($_GET['IsDroped']))
    {
        if($_GET['IsDroped'] == '0')
        {
            /* Update group info*/
            foreach($GroupACL as $GroupInfo)
            {
                $NewGroupUsers = str_replace(',' . $UserInfo['UserName'], '' , $GroupInfo['GroupUser']);
                if($NewGroupUsers == ',') $NewGroupUsers = '';
                $NewGroupUsers = my_escape_string($NewGroupUsers);
                dbUpdateRow('TestGroup', 'GroupUser', "'{$NewGroupUsers}'",  "GroupID = '{$GroupInfo[GroupID]}'");                   

                $NewGroupManagers = str_replace(',' . $UserInfo['UserName'], '' , $GroupInfo['GroupManagers']);
                if($NewGroupManagers == ',') $NewGroupManagers = '';
                $NewGroupManagers = my_escape_string($NewGroupManagers);
                dbUpdateRow('TestGroup', 'GroupManagers', "'{$NewGroupManagers}'",  "GroupID = '{$GroupInfo[GroupID]}'");                   

            } 

            /* update project info*/
            $ProjectAdminACL = dbGetList('TestProject', '', "ProjectManagers LIKE '%," . my_escape_string(my_escape_string($UserInfo['UserName'])) . ",%'");
            foreach($ProjectAdminACL as $ProjectInfo)
            {
                $NewProjectManagers = str_replace(',' . $UserInfo['UserName'], '' , $ProjectInfo['ProjectManagers']);
                if($NewProjectManagers == ',') $NewProjectManagers = '';
                $NewProjectManagers = my_escape_string($NewProjectManagers);
                dbUpdateRow('TestProject', 'ProjectManagers', "'{$NewProjectManagers}'",  "ProjectID = '{$ProjectInfo[ProjectID]}'");                   
            } 
            
            dbUpdateRow('TestUser', 'IsDroped', "'1'", 'LastEditedBy', "'{$_SESSION[TestUserName]}'", 'LastDate', 'now()', "UserID = '{$_GET[UserID]}'");
        }
        elseif($_GET['IsDroped'] == '1')
        {
            dbUpdateRow('TestUser', 'IsDroped', "'0'", 'LastEditedBy', "'{$_SESSION[TestUserName]}'", 'LastDate', 'now()', "UserID = '{$_GET[UserID]}'");
        }
    
        // add update group
        $BackUrl = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : 'AdminUserList.php';
        jsGoTo($BackUrl);
        exit;
    }
}
else if($ActionType == 'SyncUser'){

        $url = $_CFG['LDAP']['Url'];
        $tls = $_CFG['LDAP']['TTLS'];
        $binddn = $_CFG['LDAP']['BindDn'];
        $bindpw = $_CFG['LDAP']['BindPw'];

        $base = $_CFG['LDAP']['Base'];
        $userdn = "ou=peoples,$base";
        $groupdn = "ou=groups,$base";

        $ldap = new PowerLDAP($url, $tls, $binddn, $bindpw, $userdn, $groupdn);

        $Users = $ldap->listUsers();
        $Groups  = $ldap->listGroups();
        $newUsers = Array();
        $oldUsers = Array();
        foreach ($Users as $user){
            $uid= $user['UserName'];
            $email = $user['Email'];
            $nick = $user['RealName'];

            $u = dbGetRow('TestUser', '', "UserName= '{$uid}'");
            if ($u) {
                //update
                dbUpdateRow('TestUser', 'Email', "'{$email}'",  'RealName', "'$nick'", "UserName= '{$uid}'");                   
                $oldUsers []= $u;
            }
            else {
                dbInsertRow('TestUser', "'{$uid}','{$nick}','123456', '{$email}', '" . my_escape_string($_SESSION['TestUserName']) . "', now(), '" . my_escape_string($_SESSION['TestUserName']) . "', now(), '0', 'LDAP'"
            , "UserName, RealName, UserPassword, Email, AddedBy, AddDate, LastEditedBy, LastDate, IsDroped, AuthMode");
                $newUsers []= Array('UserName'=>$uid, 'Email'=>$email, 'RealName'=>$nick);
            }
        }
        $newGroups = Array();
        $oldGroups = Array();
        foreach ($Groups as $group){
            $gid = $group['GroupName'];
            $ms  = $group['members'];
            if ($ms) {
                $ms = implode(',', $ms);
                $g = dbGetRow('TestGroup', '', "GroupName= '{$gid}'");
                if ($g){
                    dbUpdateRow('TestGroup', 'GroupUser', "'{$ms}'" , 'LastEditedBy', "'" . my_escape_string($_SESSION['TestUserName']) . "'", 'LastDate', 'now()'
                            , "GroupName ='{$gid}'");
                    $oldGroups []= Array('gn'=>$gid, 'ms' => $ms);
                }else{
                    dbInsertRow('TestGroup', "'{$gid}','','{$ms}', '" . my_escape_string($_SESSION['TestUserName']) . "', now(), '" . my_escape_string($_SESSION['TestUserName']) . "', now()"
                                              , "GroupName, GroupManagers, GroupUser, AddedBy, AddDate, LastEditedBy, LastDate");
                    $newGroups []= Array('gn'=>$gid, 'ms' => $ms);

                }
            }
        }
        $TPL->assign('newUsers', $newUsers);
        $TPL->assign('oldUsers', $oldUsers);
        $TPL->assign('newGroups', $newGroups);
        $TPL->assign('oldGroups', $oldGroups);

}

$tpl =  ( $ActionType == 'AddUser' ) ? 'Admin/User.tpl' : 'Admin/Sync.tpl';

/* Create select html code */
$GroupList = testGetGroupList('GroupID <> 1');
$ACLAttrib = 'multiple="multiple" size="8" class="MultiSelect"';
$UserInfo['AuthModeName'] = $_LANG['AuthMode'][$UserInfo['AuthMode']];
$TPL->assign('UserInfo', $UserInfo);

/* Display the template file. */
$TPL->assign('NavActiveUser', ' class="Active"');
$TPL->assign('ActionType', $ActionType);
$TPL->display($tpl);
