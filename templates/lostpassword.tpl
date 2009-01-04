{include file="msgbox.tpl"}

{literal}
<SCRIPT TYPE="text/javascript">
	function validateOnSubmit() {
	var elem;
	var errs=0;
	if (!validatePresent(document.forms.lostpassword.details,  'inf_details')) errs += 1;
	return (errs==0);
	};
</SCRIPT>
{/literal}

<fieldset>
	<h5>password retreival</h5>
	<p>enter username or email address</p>

	<form name="lostpassword" method="post" action="{php}echo dirname($_SERVER['PHP_SELF']);{/php}/?lostpassword" onsubmit="return validateOnSubmit();">

		<p>
			<label for="details">details:</label>
			<input type="text" class="inputField" id="details" name="details" onchange="validatePresent(this, 'inf_details');" />
			<span id="inf_details">*</span>
		</p>
		<p>
			<input type="submit" class="inputField" name="lostpassword_submit" value="send password!" />
		</p>


	</form>
</fieldset>
