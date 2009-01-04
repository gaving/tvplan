<!-- Left content cloumn starts: news/search/images/calendar -->

<div id="leftColumn">

	{if (is_array($sitestats))}
	<dl>
		<dt>Overview</dt>
		<dd><strong>{$sitestats.available_shows}</strong> shows available in the database,</dd> 
		<dd><strong>{$sitestats.tracked_shows}</strong> of which are being actively tracked by</dd>
		<dd><strong>{$sitestats.users}</strong> registered members.</dd>
	</dl>
	{/if}

	{if (is_array($torrents))}
	<div class="leftColumnBox">
		<h1>torrents</h1>

		{section name=i loop=$torrents}
		<a {$torrents[i].overlib}>
			<ul class="progress">
				<li style="width: {$torrents[i].percent}%;">{$torrents[i].percent}%</li>
			</ul>
		</a>
		<div class="center">
			{$torrents[i].filename}
		</div>
		<br />
		{/section}

		<hr style="width: 80%; border: 1px solid #CCCCCC" />
		<div class="center">
			<strong>download:</strong> <em>{$global_download}</em> - <strong>upload:</strong> <em>{$global_upload}</em>
		</div>
		<p>&nbsp;&nbsp;</p>
	</div>
	{/if}

	{if (!empty($showsearchbox))}
	<div class="leftColumnBox">
		<h1>search</h1>
		<p class="center">
			<input type="text" size="20" class="inputField" value="Enter Terms&hellip;" onfocus="if (value=='Enter Terms&hellip;') value='';" onblur="if(value=='') value='Enter Terms&hellip;';" /> 
			<input type="submit" size="20" class="inputField" value="Go!"/> 
		</p>
	</div>
	{/if}

	{if (is_array($rss_feed))}
	<div class="leftColumnBox">
		<h1>latest rss</h1>
		<ul>
			{section name=i loop=$rss_feed}
			<li><a href="{$rss_feed[i].link}">{$rss_feed[i].title}</a></li>
			{/section}
		</ul>
	</div>
	{/if}

	<!-- Left content cloumn ends -->

	{if (isset($calendar))}
	<!-- Calendar starts -->

	{$calendar}

	<!-- Calendar ends -->
	{/if}

</div>

<!-- 
Right content column starts: content, images and whatever else you want.
Due to the way smarty templates are included, placing the below section here allows
us to add things after this and before the body.tpl without it looking stupid.
-->

<div id="rightColumn">
