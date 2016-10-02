{include file="Admin/AdminHeader.tpl"}
<body class="AdminBody">
{literal}
<style>
div.CommonForm h1{display:block;}
</style>
{/literal}
{include file="Admin/AdminTopNav.tpl"}
  <div class="AdminNav">
    &lt;<a href="AdminUserList.php">{$Lang.BackToUserList}</a>&#124;
    <strong>{$UserInfo.UserName}</strong>
  </div>
  {include file="ActionMessage.tpl"}

  <div id="Users" class="CommonForm AdminForm">
{if $newUsers}
    <br><h1>{$Lang.SyncUserNew}</h1><br>
    <dl class="Line"></dl>
    {foreach item=User from=$newUsers}
    <dl>
      <dt><label>{$User.UserName}</label></dt>
      <dd> {$User.Email} </dd>
      <dd> {$User.RealName} </dd><br>
    </dl>
    {/foreach}
    <br><br>
{/if}

{if $oldUsers}
    <br><h1>{$Lang.SyncUserOld}</h1><br>
    <dl class="Line"></dl>
    {foreach item=User from=$oldUsers}
    <dl>
      <dt><label>{$User.UserName}</label></dt>
      <dd> {$User.Email} </dd>
      <dd> {$User.RealName} </dd><br>
    </dl>
    {/foreach}
    <br><br>
{/if}

{if $newGroups}
    <br><h1>{$Lang.SyncGroupNew}</h1><br>
    <dl class="Line"></dl>
    {foreach item=Group from=$newGroups}
    <dl>
      <dt><label>{$Group.gn}</label></dt>
      <dd><label>{$Group.ms}</label></dd><br>
    </dl>
    {/foreach}
    <br><br>
{/if}

{if $oldGroups}
    <br><h1>{$Lang.SyncGroupOld}</h1><br>
    <dl class="Line"></dl>
    {foreach item=Group from=$oldGroups}
    <dl>
      <dt><label>{$Group.gn}</label></dt>
      <dd><label>{$Group.ms}</label></dd><br>
    </dl>
    {/foreach}
    <br><br>
{/if}

  </div>

{literal}
<script type="text/javascript">
//<![CDATA[
//]]>
</script>
{/literal}
</body>
</html>
