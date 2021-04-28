<script src="https://apis.google.com/js/platform.js?onload=initGooglePlatform" async defer></script>
<script>
    window.googleLogin = function() {

    };

    function initGooglePlatform() {
        gapi.load('auth2', function() {

            window.googleLogin = function() {
                gapi.auth2.authorize({
                    client_id: '<?php echo $clientId; ?>',
                    scope: 'email profile',
                    response_type: 'id_token permission'
                }, function(response) {

                    if (response.error) {
                        // An error happened!
                        return;
                    }

                    //var accessToken = response.access_token;
                    var idToken = response.id_token;

                    var form = document.createElement('form');
                    document.getElementById('google-authenticator').append(form);

                    form.action = '<?php echo $authUrl; ?>';
                    form.method = 'post';

                    var input = document.createElement('input');
                    form.append(input);

                    input.name = 'idtoken';
                    input.value = idToken;
                    input.type = 'hidden';

                    form.submit();

                });
            }

            document.getElementById('google-authenticator').innerHTML = <?php
                echo json_encode($this->template('CatLab/Accounts/authenticators/deligated/inlineform.phpt', [
                    'url' => 'javascript:googleLogin()',
                    'authentication' => $authenticator
                ]))
            ?>;
        });
    }

</script>
<span id="google-authenticator"></span>
