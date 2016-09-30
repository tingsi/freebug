<?php
$FC_ColorCounter=0;

$arr_FCColors[0] = "1941A5" ;//Dark Blue
$arr_FCColors[1] = "AFD8F8";
$arr_FCColors[2] = "F6BD0F";
$arr_FCColors[3] = "8BBA00";
$arr_FCColors[4] = "A66EDD";
$arr_FCColors[5] = "F984A1" ;
$arr_FCColors[6] = "CCCC00" ;//Chrome Yellow+Green
$arr_FCColors[7] = "999999" ;//Grey
$arr_FCColors[8] = "0099CC" ;//Blue Shade
$arr_FCColors[9] = "FF0000" ;//Bright Red
$arr_FCColors[10] = "006F00" ;//Dark Green
$arr_FCColors[11] = "0099FF"; //Blue (Light)
$arr_FCColors[12] = "FF66CC" ;//Dark Pink
$arr_FCColors[13] = "669966" ;//Dirty green
$arr_FCColors[14] = "7C7CB4" ;//Violet shade of blue
$arr_FCColors[15] = "FF9933" ;//Orange
$arr_FCColors[16] = "9900FF" ;//Violet
$arr_FCColors[17] = "99FFCC" ;//Blue+Green Light
$arr_FCColors[18] = "CCCCFF" ;//Light violet
$arr_FCColors[19] = "669900" ;//Shade of green

//getFCColor method helps return a color from arr_FCColors array. It uses
//cyclic iteration to return a color from a given index. The index value is
//maintained in FC_ColorCounter

function getFCColor()
{
	//accessing the global variables
	global $FC_ColorCounter;
	global $arr_FCColors;
	
	//Update index
	$FC_ColorCounter++;
	//Return color
	return('#' . $arr_FCColors[$FC_ColorCounter % count($arr_FCColors)]);
}

function resetColor()
{
	global $FC_ColorCounter;
	$FC_ColorCounter = 0;
}
