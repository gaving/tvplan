{include file="msgbox.tpl"}

{if (is_array($show_index))}
<p>&nbsp;&nbsp;</p>
<table class="specificshow" align="center" cellspacing="0">
	<tr class="specificshowheader">
		<td colspan="1">Name</td>
		<td colspan="1">Next/latest air date</td>
	</tr>
	{section name=i loop=$show_index}
	<tr class="specificshowrow">
		<td><a href="?browse,{$show_index[i].epguide_name}">{$show_index[i].name}</a></td>
		<td>{$show_index[i].next_air}</td>
	</tr>
	{/section}
</table>
{/if}


<p>&nbsp;&nbsp;</p>
<p>&nbsp;&nbsp;</p>

<div class="bugFix">
	&nbsp;
</div>

  </div>
