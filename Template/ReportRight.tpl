{include file="ReportHeader.tpl"}
<body onload="reSize();" onResize="reSize();" class="{$StatMode} PaddingBoy" style ="overflow:auto;">

<div style="width:75%; height:250px; margin:0 5px" id="maindiv">

{foreach from=$FCScriptList key=Key item=FCScript}
    <div>
    <h3>{$FCLegendList[$Key]}</h3>
    {$FCScript}
    {$FCNoteList[$Key]}
    </div><br>
{/foreach}

</div>

{literal}
<script>
function reSize()
{
    var h1 = document.body.clientHeight;
    var h2 = document.documentElement.clientHeight;
    var isXhtml = (h2>0)?true:false;
    var body = (isXhtml && h2>h1)?document.documentElement:document.body;
    xajax.$('maindiv').style.height = body.clientHeight-13 + 'px';
}
</script>
{/literal}

</body>
</html>
