{include file="Header.tpl"}
<body id="BGColor" onload="this.focus();initShowGotoBCR();">
  <div id="TopNavMain">
    <div id="TopNavLogo">
      <a href="./" target="_top"><img src="Image/logo.png" title={$Lang.ProductName} /></a>
    </div>
    <div id="TopNavAbout">
      <span>{$Lang.Welcome},</span>
      <span id="UserName">{$TestRealName}</span>
      {if $TestIsAdmin || $TestIsProjectAdmin}
      <a href="Admin/" target="_blank">{$Lang.Admin}</a>{/if}
      <a href="Logout.php?Logout=Yes">{$Lang.Logout}</a>
    </div>
    <div id="ProjectList">{$TopNavProjectList}</div>
    <div id="Open">
      <a href="Bug.php?ActionType=OpenBug" id="OpenBug" class="BigButton OpenBug" target="_blank" {if $TestMode neq 'Bug'}style="display:none;"{/if}>
        {$Lang.OpenBug}
      </a>
      <a href="Case.php?ActionType=OpenCase" id="OpenCase" class="BigButton OpenCase" target="_blank"{if $TestMode neq 'Case'} style="display:none;"{/if}>
        {$Lang.OpenCase}
      </a>
    </div>
  </div>
</body>
</html>