{include file="msgbox.tpl"}

{literal}
<SCRIPT TYPE="text/javascript">
	function validateOnSubmit() {
	var elem;
	var errs=0;
	if (!validateEmail(document.forms.add_user.email, 'inf_email', true)) errs += 1;
	if (!validatePassword(document.forms.add_user.password,  'inf_password', true)) errs += 1;
	if (!validatePresent(document.forms.add_user.username,  'inf_username')) errs += 1;

	return (errs==0);
	};
</SCRIPT>
{/literal}

<fieldset>

	<h5>user management</h5>
	<p>modify/remove users</p>

	<form method="post" action="?admin">

		<p>
			<label for="what">username:</label>
			<select name="username">
				{section name=i loop=$user_list}
				<option value="{$user_list[i]}">{$user_list[i]}</option>
				{/section}
			</select>

			<input type="submit" class="inputField" name="updateuser_submit" value="modify" DISABLED />
			<input type="submit" class="inputField" name="deleteuser_submit" value="delete" />
		</p>

	</form>

	<h5>user management</h5>
	<p>add user</p>

	<form name="add_user" method="post" action="?admin" onsubmit="return validateOnSubmit();">

		<p>
			<label for="username">username:</label>
			<input type="text" class="inputField" id="username" name="adduser_username" onchange="validatePresent(this, 'inf_username');" />
			<span id="inf_username">*</span>
		</p>
		<p>
			<label for="password">password:</label>
			<input type="password" class="inputField" id="password" name="adduser_password" onchange="validatePassword(this, 'inf_password', true);" />
			<span id="inf_password">*</span>
		</p>
		<p>
			<label for="email">email:</label>
			<input type="text" class="inputField" id="email" name="adduser_email" onchange="validateEmail(this, 'inf_email', true);"/>
			<span id="inf_email" >*</span>
		</p>
		<p>
			<label for="email">admin status:</label>
			<input type="checkbox" class="fixieborder" name="adduser_admin" />
		</p>

		<p>
			<input type="submit" class="inputField" name="adduser_submit" value="add user!" />
		</p>


	</form>

	<h5>database manipulation</h5>
	<p>administrate the databases</p>

	<form method="post" action="?admin">
		<p>
			<label for="rebuildshowlist">rebuild show list</label>
			<input type="checkbox" class="fixieborder" name="rebuildshowlist" DISABLED />
		</p>

		<p>
			<input type="submit" class="inputField" name="database_submit" value="apply changes!" />
		</p>

	</form>

</fieldset>
