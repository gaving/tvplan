{include file="msgbox.tpl"}

{literal}
<SCRIPT TYPE="text/javascript">
	function validateOnSubmit() {
	var elem;
	var errs=0;
	if (!validatePresent(document.forms.login.password,  'inf_password')) errs += 1;
	if (!validatePresent(document.forms.login.username,  'inf_username')) errs += 1;

	return (errs==0);
	};
</SCRIPT>
{/literal}

<fieldset>

	<h5>login to your account</h5>
	<p>enter your details (<a href="?register">not registered?</a>)</p>

	<form name="login" method="post" action="{php}echo dirname($_SERVER['PHP_SELF']);{/php}/" onsubmit="return validateOnSubmit();">
		<p>
			<label for="username">username:</label>
			<input type="text" class="inputField" id="username" name="username" ONCHANGE="validatePresent(this, 'inf_username');" />
			<span id="inf_username">*</span>
		</p>
		<p>
			<label for="password">password:</label>
			<input type="password" class="inputField" id="password" name="password" ONCHANGE="validatePresent(this, 'inf_password');" />
			<span id="inf_password">*</span>
		</p>
		<p>
			<label for="remember">remember me:</label>
			<input type="checkbox" class="fixieborder" name="remember" />
		</p>
		<p>
			<input type="submit" class="inputField" name="login_submit" value="login!" />
		</p>

	</form>
</fieldset>
