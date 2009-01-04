{include file="msgbox.tpl"}

{if (empty($enable_registration))}
<p>
	Sorry this section has been intetionally disabled by an admin.
</p>
{else}
{literal}
<SCRIPT TYPE="text/javascript">
	function validateOnSubmit() {
	var elem;
	var errs=0;
	if (!validateEmail(document.forms.register.email, 'inf_email', true)) errs += 1;
	if (!validatePassword(document.forms.register.password,  'inf_password', true)) errs += 1;
	if (!validatePresent(document.forms.register.username,  'inf_username')) errs += 1;

	return (errs==0);
	};
</SCRIPT>
{/literal}

<fieldset>
	<h5>register an account</h5>
	<p>enter your details</p>

	<form name="register" method="post" action="{php}echo dirname($_SERVER['PHP_SELF']);{/php}/?register" onsubmit="return validateOnSubmit();">

		<p>
			<label for="username">username:</label>
			<input type="text" class="inputField" id="username" name="register_username" onchange="validatePresent(this, 'inf_username');" />
			<span id="inf_username">*</span>
		</p>
		<p>
			<label for="password">password:</label>
			<input type="password" class="inputField" id="password" name="register_password" onchange="validatePassword(this, 'inf_password', true);" />
			<span id="inf_password">*</span>
		</p>
		<p>
			<label for="email">email:</label>
			<input type="text" class="inputField" id="email" name="register_email" onchange="validateEmail(this, 'inf_email', true);" />
			<span id="inf_email">*</span>
		</p>
		<p>
			<label for="remember">login now:</label>
			<input type="checkbox" class="fixieborder" name="automatic_login" />
		</p>
		<p>
			<input type="submit" class="inputField" name="register_submit" value="register!" />
		</p>


	</form>
</fieldset>
{/if}
