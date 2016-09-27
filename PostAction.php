<?php
/**
 * BugFree is free software under the terms of the FreeBSD License.
 *
 * deal the bug, case, result post info.
 *
 * @link        http://www.bugfree.org.cn
 * @package     BugFree
 */
/* Init BugFree system. */
require('Include/Init.inc.php');

//print_r($_SESSION);
ob_start();

$TPL->display('PostAction.tpl');
ob_flush();
flush();

if(!$_POST)
{
    sysObFlush(jsGoto("index.php",'parent'));
    exit;
}

if($_CFG['DebugMode'])
{
    echo "<pre>";
    echo "-------------------------POST--------------------------\n";
    print_r($_POST);
    echo "----------------------Upload File--------------------------\n";
    print_r($_FILES);
}



if(isset($_POST['ToDisabledObj']))
{
    disableObj($_POST['ToDisabledObj']);
}

//sysObFlushJs("xajax.loadingFunction();");

//Check upload file
$CheckUploadFile = sysCheckUploadFile();
if(!$CheckUploadFile['Bingle'])
{
    sysObFlush(jsAlert(join('\n', $CheckUploadFile['FailedMsg']),false));
    enableObj($_POST['ToDisabledObj']);
    sysObFlushJs("parent.NeedToConfirm=true;");
    exit;
}
if($CheckUploadFile['ValidFileCount']>0)
{
    $UploadFile = true;
}
else
{
    $UplodaFile = false;
}

if($_GET['Action'] == 'OpenBug')
{
    $OpenBugMsg = testOpenBug($_POST);
    if(!$OpenBugMsg['Bingle'])
    {
        $FailedMsg = join('\n',$OpenBugMsg['FailedMsg']);
        sysObFlushJs("alert('{$FailedMsg}');");
    }
    else
    {
        if($UploadFile)
        {
            $UploadFileList = sysUploadFile($_POST['ProjectID'], $OpenBugMsg['ActionID']);
        }
        testRefreshParent();
        sysObFlush(jsGoto("Bug.php?BugID={$OpenBugMsg['BugID']}",'parent'));
        exit;
    }
}
elseif(in_array($_POST['ActionType'], array('Edited','Resolved','Closed','Activated')) && $_POST['ActionObj'] == 'Bug')
{
    $ActionFunctionName = 'testEditBug';
    $EditBugMsg = $ActionFunctionName($_POST, $UploadFile);
    if(!$EditBugMsg['Bingle'])
    {
        $FailedMsg = join('\n',$EditBugMsg['FailedMsg']);
        sysObFlushJs("alert('{$FailedMsg}');");
    }
    else
    {
        if($UploadFile)
        {
            $UploadFileList = sysUploadFile($_POST['ProjectID'], $EditBugMsg['ActionID']);
        }
        testRefreshParent();
        sysObFlush(jsGoto("Bug.php?BugID={$_POST['BugID']}",'parent'));
        exit;
    }
}
elseif($_GET['Action'] == 'EditMyInfo')
{
    $EditMsg = testEditUser($_POST);
    if(!$EditMsg['Bingle'])
    {
        $EditMsg = join('\n',$EditMsg['FailedMsg']);
        sysObFlushJs("alert('{$EditMsg}');");
    }
    else
    {
        sysObFlushJs("alert('{$_LANG[SuccessEditMyInfo]}');");
        sysObFlush(jsGoto("EditMyInfo.php",'parent'));
        exit;
    }
}


function testRefreshParent()
{
    $JS = <<<EOT
try{
var parentWin=window.parent;
var openerWin=parentWin.opener;
var indexWin=openerWin.parent;
indexWin.LeftBottomFrame.location.reload();
indexWin.RightBottomFrame.location.href=indexWin.RightBottomFrame.location.href;
}
catch(e){}
EOT;
sysObFlushJs($JS);
}


function testFilterCaseStep($StepArray)
{
    $ReturnStepArray = array();
    foreach($StepArray as $Key => $Value)
    {
        $Value = trim($Value);
        $Value = str_replace('\r', '', $Value);
        $Value = str_replace('\n', '', $Value);

        if($Value != '')
        {
            $ReturnStepArray[$Key] = $Value;
        }
    }
    return $ReturnStepArray;
}

if(isset($_POST['ToDisabledObj']))
{
    enableObj($_POST['ToDisabledObj']);
    sysObFlushJs("parent.NeedToConfirm=true;");
}

//sysObFlushJs("xajax.doneLoadingFunction();");

function disableObj($ObjIds)
{
    $ObjIdList = explode(',', $ObjIds);
    foreach($ObjIdList as $ObjId)
    {
        sysObFlushJs("parent.xajax.$('{$ObjId}').disabled = true;");
    }
}

function enableObj($ObjIds)
{
    $ObjIdList = explode(',', $ObjIds);
    foreach($ObjIdList as $ObjId)
    {
        sysObFlushJs("parent.xajax.$('{$ObjId}').disabled = false;");
    }
}

?>
