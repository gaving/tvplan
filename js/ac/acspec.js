
  		function oncomplete() {
  			document.f.submit();
  		}
  		function setup() {
  			var ac1 = new AC('show', 'show', oncomplete);
  			ac1.enable_unicode();

  			document.f.show.focus();
  			document.getElementById('huh').style.visibility='hidden'
  		}
  		



