{include file="msgbox.tpl"}

{literal}
<SCRIPT TYPE="text/javascript">
	function validateOnSubmit() {
	var elem;
	var errs=0;
	if (!validatePassword(document.forms.prefs.password,  'inf_password')) errs += 1;
	if (!validatePassword(document.forms.prefs.cpassword,  'inf_cpassword')) errs += 1;
	if (!validateConfirmPassword(document.forms.prefs.password, document.forms.prefs.cpassword,  'inf_password')) errs += 1;

	return (errs==0);
	};
</SCRIPT>
{/literal}

<fieldset>

	<h5>monitor shows</h5>
	<p>choose which files to shows to monitor (add to calendar, etc)</p>

	<p>
		<label for="what">show:</label>

		<form method="post" action="?config">
			<select name="show_name">
				{if (!is_array($shows))}
				<option value="no">(Nothing added!)</option>
				{/if}
				{section name=i loop=$shows}
				<option value="{$shows[i]}">{$shows[i]}</option>
				{/section}
			</select>

			<input type="submit" class="inputField" name="delete_submit" value="delete" {if (!is_array($shows))}DISABLED{/if} /> /
			<input type="submit" class="inputField" name="update_submit" value="update" {if (!is_array($shows))}DISABLED{/if} />
			<input type="submit" class="inputField" name="updateall_submit" value="update all" {if (!is_array($shows))}DISABLED{/if} />
		</form>

		<p>
			<label for="add_show">add (autocomplete):</label>
			<form name="f" method="post" action="?config">
				<input autocomplete="off" type="text" id="show" name="show" value="" size="50" /> (enter selects show)<input type="text" id="huh" size="1" />
			</form>
		</p>
	</p>

	<h5>sidebar preferences</h5>
	<p>what to show on the left column menu, (search, torrents, rss, etc)</p>

	<form method="post" name="prefs" action="?config" onsubmit="return validateOnSubmit();">

		<p>
			<label for="checknews">enable news:</label>
			<input type="checkbox" class="fixieborder" name="checknews" {if (isset($checknews))}CHECKED{/if} />
		</p>
		<p>
			<label for="showsearchbox">show search box:</label>
			<input type="checkbox" class="fixieborder" name="showsearchbox" {if (isset($showsearchbox))}CHECKED{/if} />
		</p>
		<p>
			<label for="newzbinlinks">use newbin links:</label>
			<input type="checkbox" class="fixieborder" name="newzbinlinks" {if (isset($newzbinlinks))}CHECKED{/if} />
		</p>
		<p>
			<label for="enabletips">show 'tip of the day':</label>
			<input type="checkbox" class="fixieborder" name="enabletips" {if (isset($enabletips))}CHECKED{/if} />
		</p>
		<p>

			<label for="calendarstyle">calendar style:</label>
			<select name="calendarstyle">
				<option value="balloon" {if ($calendarstyle == "balloon")}selected="selected"{/if}>balloon</option>
				<option value="overlib" {if ($calendarstyle == "overlib")}selected="selected"{/if}>standard overlib</option>
			</select>

		</p>
		<h5>timeszone</h5>
		<p>configure your local timezone</p>

		<p>
			<label for="time">local time:</label>
			{$server_date}
		</p>
		<p>
			<label for="offset">gmt offset:</label>
			<input id="offset" type="text" class="inputField" name="offset" size="3" {if (isset($offset))}value="{$offset}"{/if} /> (e.g. -4, +5)
			{if (isset($offset))}<input type="hidden" name="offset_hidden" value="{$offset}" />{/if}
		</p>

		<h5>torrent stats</h5>
		<p>reads your torrent details from an azureus formatted file</p>

		<p>
			<label for="showtorrents">show torrents:</label>
			<input type="checkbox" class="fixieborder" name="showtorrents" onclick="toggle('showseeding'); toggle('torrentfile')" {if (isset($showtorrents))}CHECKED{/if} />
		</p>

		<p>
			<label for="showseeding">(include seeded):</label>
			<input id="showseeding" type="checkbox" class="fixieborder" name="showseeding" {if (isset($showseeding))}CHECKED{/if} />
		</p>

		<p>
			<label for="torrentfile">file (local or remote):</label>
			<input id="torrentfile" type="text" class="inputField" name="torrentfile" {if (isset($torrentfile))}value="{$torrentfile}"{/if}/>
			{if (isset($showtorrents))}<input type="hidden" name="torrentfile_hidden" value="{$torrentfile}" />{/if}
		</p>

	</p>

	<h5>rss details</h5>
	<p>sidebar box to grab the latest headers from a feed you specify</p>

	<p>
		<label for="checkrss">enable rss feed:</label>
		<input type="checkbox" class="fixieborder" name="checkrss" onclick="toggle('rssserver'); toggle('rssusername'); toggle('rsspassword');"  {if (isset($checkrss))}CHECKED{/if} />
	</p>
	<p>
		<label for="rssserver">server (excl http):</label>
		<input id="rssserver" type="text" class="inputField" name="rssserver" {if (isset($rssserver))}value="{$rssserver}"{/if} />
		{if (isset($rssserver))}<input type="hidden" name="rssserver_hidden" value="{$rssserver}" />{/if}
	</p>
	<p>
		<label for="rssusername">username:</label>
		<input id="rssusername" type="text" class="inputField" name="rssusername" {if (isset($rssusername))}value="{$rssusername}"{/if}/>
		{if (isset($rssusername))}<input type="hidden" name="rssusername_hidden" value="{$rssusername}" />{/if}
	</p>
	<p>
		<label for="rsspassword">password:</label>
		<input id="rsspassword" type="password" class="inputField" name="rsspassword" {if (isset($rsspassword))}value="{$rsspassword}"{/if}/>
		{if (isset($rsspassword))}<input type="hidden" name="rsspassword_hidden" value="{$rsspassword}" />{/if}
	</p>

	<h5>change password</h5>
	<p>if you would like to change your password, enter your new password here.</p>

	<p>
		<label for="new_password">password:</label>
		<input id="new_password" autocomplete="off" type="password" class="inputField" name="new_password"  onchange="validatePassword(this, 'inf_password');" />
		<span id="inf_password">!</span>
	</p>
	<p>
		<label for="new_cpassword">confirm:</label>
		<input id="new_cpassword" autocomplete="off" type="password" class="inputField" name="confirm_password"  onchange="validatePassword(this, 'inf_cpassword'); validateConfirmPassword(document.forms.prefs.password, document.forms.prefs.cpassword,  'inf_cpassword')" />
		<span id="inf_cpassword">!</span>
	</p>
	
	<h5>database manipulation</h5>
	<p>administrate your databases</p>

	<p>
		<label for="nukenews">nuke news db</label>
		<input type="checkbox" class="fixieborder" name="nuke_submit" />
	</p>
	
	<p>&nbsp;&nbsp;</p>
	<p>
		<input type="submit" class="inputField" name="submit" value="apply changes" />
	</p>


</fieldset>

</div>
