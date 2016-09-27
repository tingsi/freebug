<?php
require('Include/Class/XmlParse.class.php');

function ExportXML($CaseExportList,$CaseExportColumnArray,$FieldsArray){
	$RowCount = count($CaseExportList)+1;
	$ColumnCount = count($CaseExportColumnArray);

	$Content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                 <?mso-application progid=\"Excel.Sheet\"?>
                 <Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
                 xmlns:o=\"urn:schemas-microsoft-com:office:office\"
                 xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
                 xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
                 xmlns:html=\"http://www.w3.org/TR/REC-html40\">
                 <DocumentProperties xmlns=\"urn:schemas-microsoft-com:office:office\">
                  <Created>1996-12-17T01:32:42Z</Created>
                  <LastSaved>2009-11-21T14:55:15Z</LastSaved>
                  <Version>11.9999</Version>
                 </DocumentProperties>
                 <OfficeDocumentSettings xmlns=\"urn:schemas-microsoft-com:office:office\">
                  <RemovePersonalInformation/>
                 </OfficeDocumentSettings>
                 <ExcelWorkbook xmlns=\"urn:schemas-microsoft-com:office:excel\">
                  <WindowHeight>4530</WindowHeight>
                  <WindowWidth>8505</WindowWidth>
                  <WindowTopX>480</WindowTopX>
                  <WindowTopY>120</WindowTopY>
                  <AcceptLabelsInFormulas/>
                  <ProtectStructure>False</ProtectStructure>
                  <ProtectWindows>False</ProtectWindows>
                 </ExcelWorkbook>
                 <Styles>
                  <Style ss:ID=\"Default\" ss:Name=\"Normal\">
                   <Alignment ss:Vertical=\"Bottom\"/>
                   <Borders/>
                   <Font ss:FontName=\"\" x:CharSet=\"134\" ss:Size=\"12\"/>
                   <Interior/>
                   <NumberFormat/>
                   <Protection/>
                  </Style>
                  <Style ss:ID=\"s21\">
                   <Alignment ss:Vertical=\"Bottom\" ss:WrapText=\"1\"/>
                  </Style>
                 </Styles>
                 <Worksheet ss:Name=\"Sheet1\">
                  <Table ss:ExpandedColumnCount=\"".$ColumnCount ."\" ss:ExpandedRowCount=\"" . $RowCount ."\" x:FullColumns=\"1\"
                   x:FullRows=\"1\" ss:DefaultColumnWidth=\"54\" ss:DefaultRowHeight=\"14.25\">";

   	$TempStr = "\n<Row>";
   	foreach($CaseExportColumnArray as $ExportItem)
   	{ 
       		$TempStr .=" \n<Cell><Data ss:Type=\"String\">" . $FieldsArray[$ExportItem] . "</Data></Cell>\n";
   	}
   	$TempStr .= "</Row>\n";
   	$arr_search = array('<','>',"'",'&', '"',"\n");
   	$arr_replace = array('&lt;','&gt;','&apos;','&amp;','&quot;', '&#10;');
        $arr_search1 = array('&amp;lt;','&amp;gt;','&amp;quot;','&amp;amp;','&amp;apos;');
   	$arr_replace1 = array('&lt;','&gt;','&quot;', '&amp;','&apos;');
 
    
   	foreach($CaseExportList as $CaseItem)
   	{
       		$TempStr .= "\n<Row>";
       		foreach($CaseExportColumnArray as $Column)
       		{         
         	   $TempStr .=" \n<Cell><Data ss:Type=\"String\">" . str_ireplace($arr_search1,$arr_replace1,str_ireplace($arr_search,$arr_replace,$CaseItem[$Column])) . "</Data></Cell> \n";
       		}
       		$TempStr .= "</Row>\n";
   	}

       
	$Content .= $TempStr;
	$Content .= "</Table>
                  <WorksheetOptions xmlns=\"urn:schemas-microsoft-com:office:excel\">
                   <Selected/>
                   <Panes>
                    <Pane>
                     <Number>3</Number>
                     <ActiveRow>1</ActiveRow>
                     <ActiveCol>1</ActiveCol>
                    </Pane>
                   </Panes>
                   <ProtectObjects>False</ProtectObjects>
                   <ProtectScenarios>False</ProtectScenarios>
                  </WorksheetOptions>
                 </Worksheet>
		 </Workbook>";
	return $Content;
}

function GetValidateColumns($ItemRow,$ImportColumnArray){ //判断Case的ProjectID、ModuleID和CaseTitle是否合法，并返回

    $ProjectModules = array();
    $ProjectName = trim($ItemRow[array_search('ProjectName', $ImportColumnArray)]);

    $CaseProjectRow = dbGetRow('TestProject', 'ProjectID', "ProjectName='{$ProjectName}'");

    if($CaseProjectRow)
    {
       $ProjectModules['ProjectID'] = $CaseProjectRow['ProjectID'];
    }
    else{

        return NULL;
    }

    if(!in_array($ProjectModules['ProjectID'],array_keys(baseGetUserACL($_SESSION['TestUserName']))) )
        return NULL; //没有权限



    $ProjectModules['ModuleID']=-1; //初始化为-1
    $ModuleListArray = testGetProjectModuleList($ProjectModules['ProjectID'],'Case');
    foreach($ModuleListArray as $Item)
    {
         if(trim($ItemRow[array_search('ModulePath', $ImportColumnArray)]) == trim($Item['NamePath']))
         {
             $ProjectModules['ModuleID'] = $Item['ModuleID'];
             break;
         }
    }

    if($ProjectModules['ModuleID']==-1)//模块路径不存�?
        return NULL;

    $ProjectModules['CaseTitle'] = trim($ItemRow[array_search('CaseTitle', $ImportColumnArray)]);

    if(!$ProjectModules['CaseTitle'])//Case标题为空
    {         return NULL;
    }

    return $ProjectModules;
}
?>
