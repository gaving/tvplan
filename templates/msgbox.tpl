{if (is_array($msg))}
{section name=i loop=$msg}
<div class="
	{if $msg[i].type=="error"}box errorBox
	{elseif $msg[i].type=="info"}box infoBox
	{elseif $msg[i].type=="tip"}box tipBox
	{elseif $msg[i].type=="nuke"}box nukeBox
	{/if}">
	{$msg[i].text}
</div>
{/section}	    
{/if}
