  <!-- Header box starts: page title and top link bar -->
  <div id="headerBox">
    <div id="headerLeft">
      <div class="verticalSpacer">&nbsp;</div>
      {$title}
    </div>
    <div id="headerRight">
      <div class="verticalSpacer">&nbsp;</div>
      {if (!empty($logged_in))}
      <a href="{php}echo dirname($_SERVER['PHP_SELF']);{/php}/?" title="return to home" class="home">home</a> <span class="noDisplay"> | </span>
      <a href="{php}echo dirname($_SERVER['PHP_SELF']);{/php}/?browse" title="view show index" class="shows">browse</a> <span class="noDisplay"> | </span>
      <a href="{php}echo dirname($_SERVER['PHP_SELF']);{/php}/?config" title="user configuration" class="config">config</a> <span class="noDisplay"> | </span>
      {if (!empty($admin))}
      	<a href="{php}echo dirname($_SERVER['PHP_SELF']);{/php}/?admin" title="admin panel" class="admin">admin</a> <span class="noDisplay"> | </span>
      {/if}
      <a href="{php}echo dirname($_SERVER['PHP_SELF']);{/php}/?logout" title="logout ({$username})" class="logout">logout ({$username})</a> <span class="noDisplay"> | </span>
      {else}
      {if (!empty($enable_registration))}
      <a href="{php}echo dirname($_SERVER['PHP_SELF']);{/php}/?register" title="create an account" class="register">register</a> <span class="noDisplay"> | </span>
      {/if}
      <a href="{php}echo dirname($_SERVER['PHP_SELF']);{/php}/?lostpassword" title="password retrieval!" class="retrieval">help</a> <span class="noDisplay"> | </span>
      <a href="{php}echo dirname($_SERVER['PHP_SELF']);{/php}/?" title="login!" class="login">login</a> <span class="noDisplay"> | </span>
      {/if}
    </div>
  </div>
