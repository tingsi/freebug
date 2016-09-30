<?php
/**
 * BugFree is free software under the terms of the FreeBSD License.
 *
 * report right scripts.
 *
 * @link        http://www.bugfree.org.cn
 * @package     BugFree
 */
/* Init BugFree system. */
require('Include/Init.inc.php');
require('Include/FC_Colors.php');
require('Include/FuncStat.inc.php');
require('Include/Charts.php');

$Width = 320;
$Height = 240;

$ReportMode = $_GET['ReportMode'] == '' ? 'Bug' : $_GET['ReportMode'];
if($_POST['ReportSubmit'])
{
    $_SESSION[$ReportMode.'ReportTypeList'] = $_POST['ReportTypeList'];
}
$ReportTypeList = $_SESSION[$ReportMode.'ReportTypeList'] == "" ? array() : $_SESSION[$ReportMode.'ReportTypeList'];

$WHERE = array();
$WHERE[] = $_SESSION['TestUserACLSQL'];
$WHERE[] = "IsDroped = '0'";
$ReportCondition = $_SESSION[$ReportMode . 'QueryCondition'];
$WHERE[] = $ReportCondition;
$ReportCondition = join(' AND ', $WHERE);

$FCScriptList = array();
$FCLegendList = array();
$FCNoteList = array();
$FCScriptCount = 0;
foreach($ReportTypeList as $Key => $ReportType)
{
    $ReportFunc = 'report' . $ReportType;
    $NoteStr = '';
    $DisplayNullValue = true;
    if(function_exists($ReportFunc))
    {
        $DataSetList = $ReportFunc($ReportCondition);
        $chartType = $_CFG['ReportTypeSWF'][$ReportType]; //'bar','pie' from 'bugs','case'
        $GraphOption = $_CFG['ReportGraphOption'][$chartType];
        if(!empty($_CFG['ReportTypeGraphOption'][$ReportType]))
        {
            $GraphOption = array_merge($GraphOption,$_CFG['ReportTypeGraphOption'][$ReportType]);
        }
        if(!isset($GraphOption['caption']))
        {
            $GraphOption['caption']   = $_LANG["{$ReportMode}ReportType"][$ReportType];
        }
        if(!isset($GraphOption['xAxisName']))
        {
            $GraphOption['xAxisName'] = '';
        }
        if(!isset($GraphOption['yAxisName']))
        {
            $GraphOption['yAxisName'] = $_LANG["Report{$ReportMode}Count"];
        }

        if(preg_match('/pie/i',$chartType))
        {
            $NoteStr = reportCreatePieNoteStr($DataSetList);
            $DisplayNullValue = false;
        }

        $caption   = $GraphOption['caption'];
        $xAxis     = $GraphOption['xAxisName'];
        $yAxis     = $GraphOption['yAxisName'];

        $dataStr = reportCreateDataJsonStr($DataSetList,$caption);

        $FCScriptList[$Key] = renderChart($chartType, $dataStr, $Key, $Width, $Height);
        $FCLegendList[$Key] = $_LANG[$ReportMode.'ReportType'][$ReportType];
        $FCNoteList[$Key] = $NoteStr;
        $FCScriptCount ++;
    }
}

$TPL->assign('FCScriptCount', $FCScriptCount - 1);
$TPL->assign('FCScriptList', $FCScriptList);
$TPL->assign('FCLegendList', $FCLegendList);
$TPL->assign('FCNoteList', $FCNoteList);

/* Display. */
$TPL->display('ReportRight.tpl');
