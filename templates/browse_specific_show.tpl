{include file="msgbox.tpl"}

{if (is_array($specific_show_data))}
<div class="specificshowtitle">
	<a href="http://epguides.com/{$specific_show_data[0].show_link}">
		<img src="http://epguides.com/{$specific_show_data[0].show_link}/cast.jpg" alt="Cast" title="Cast" />
	</a><br />
	{$specific_show_data[0].show_name}
</div>

<p>&nbsp;&nbsp;</p>

<table class="specificshow" align="center" cellspacing="0">
	<tr class="specificshowheader">
		<td colspan="1">Air Date</td>
		<td colspan="1">Episode Number</td>
		<td colspan="1">Episode Name</td>
		<td colspan="1">URL</td>
	</tr>

	{section name=i start=1 loop=$specific_show_data}

	{if $specific_show_closest_date > $specific_show_data[i].date}
	<tr class="specificshowrow" style="background-color: #99CCFF;">
		{elseif $specific_show_closest_date == $specific_show_data[i].date}
		<tr class="specificshowrow" style="background-color: #FFF999;">
			{elseif $specific_show_closest_date < $specific_show_data[i].date}
			<tr class="specificshowrow" style="background-color: #FF9999;">
				{else}
				<tr class="specificshowrow">
					{/if}

					<td>{$specific_show_data[i].date}</td>
					<td style="text-align: center">{$specific_show_data[i].episode}</td>
					<td>{$specific_show_data[i].name}</td>
					<td>
						<a href="{$specific_show_data[i].url}">
							<img src="img/url.gif" alt="{$specific_show_data[i].url}" title="{$specific_show_data[i].url}" />
						</a>
					</td>
				</tr>
				{/section}
			</table>

			<p>&nbsp;&nbsp;</p>
			<p>&nbsp;&nbsp;</p>
			{/if}


			<!-- IE bug fix; make sure this is the last thing before the closing .rightColumn </div> -->
		<div class="bugFix">
			&nbsp;
		</div>

	</div>
