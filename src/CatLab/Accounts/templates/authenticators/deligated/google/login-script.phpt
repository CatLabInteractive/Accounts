<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>

	var googleLoginFormSubmitted = false;
	window.onload = function () {
		google.accounts.id.initialize({
			client_id: '<?php echo $clientId; ?>',
			scope: 'email profile',
			response_type: 'id_token permission',
            callback: function(response) {

                if (response.error) {
                    // An error happened!
                    return;
                }

				// already submitting? ignore.
				if (googleLoginFormSubmitted) {
					return;
				}

                //var accessToken = response.access_token;
                var credential = response.credential;

                var form = document.createElement('form');
                document.body.appendChild(form);

                form.action = '<?php echo $authUrl; ?>';
                form.method = 'post';

                var input = document.createElement('input');
                form.appendChild(input);

                input.name = 'credential';
                input.value = credential;
                input.type = 'hidden';

				var token = document.createElement('input');
				form.appendChild(token);

				token.name = 'csfr-token';
				token.value = '<?php echo $csfr_token; ?>';
				token.type = 'hidden';

                form.submit();
				googleLoginFormSubmitted = true;

            }
		});

		var p = document.createElement('p');
		p.className = 'authenticator google';

		document.getElementById('google-authenticator').appendChild(p);

		google.accounts.id.renderButton(p, {
			theme: "filled_blue",
            type: "<?php echo $buttonType; ?>",
            size: "<?php echo $buttonSize; ?>",
            width: 400
		});

		google.accounts.id.prompt();
	}

</script>
<span id="google-authenticator"></span>
