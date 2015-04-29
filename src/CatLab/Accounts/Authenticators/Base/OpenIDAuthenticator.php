<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 30/04/15
 * Time: 0:57
 */

namespace CatLab\Accounts\Authenticators\Base;


use CatLab\Accounts\Models\DeligatedUser;
use Neuron\Net\Response;
use Neuron\URLBuilder;

abstract class OpenIDAuthenticator
	extends DeligatedAuthenticator {

	private function getStore () {

		/**
		 * This is where the example will store its OpenID information.
		 * You should change this path if you want the example store to be
		 * created elsewhere.  After you're done playing with the example
		 * script, you'll have to remove this directory manually.
		 */
		$store_path = null;
		if (function_exists('sys_get_temp_dir')) {
			$store_path = sys_get_temp_dir();
		}
		else {
			if (strpos(PHP_OS, 'WIN') === 0) {
				$store_path = $_ENV['TMP'];
				if (!isset($store_path)) {
					$dir = 'C:\Windows\Temp';
				}
			}
			else {
				$store_path = @$_ENV['TMPDIR'];
				if (!isset($store_path)) {
					$store_path = '/tmp';
				}
			}
		}
		$store_path .= DIRECTORY_SEPARATOR . '_php_consumer_test';

		if (!file_exists($store_path) &&
			!mkdir($store_path)) {
			print "Could not create the FileStore directory '$store_path'. ".
				" Please check the effective permissions.";
			exit(0);
		}
		$r = new \Auth_OpenID_FileStore($store_path);

		return $r;
	}

	private function getConsumer () {
		/**
		 * Create a consumer object using the store object created
		 * earlier.
		 */
		$store = $this->getStore ();
		$r = new \Auth_OpenID_Consumer($store);
		return $r;
	}

	private function getScheme () {
		$scheme = 'http';
		if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
			$scheme .= 's';
		}
		return $scheme;
	}

	private function getReturnTo () {
		return URLBuilder::getAbsoluteURL ($this->module->getRoutePath () . '/login/' . $this->getToken (), array ('finish' => 1));
	}

	private function getTrustRoot () {
		return URLBuilder::getAbsoluteURL ($this->module->getRoutePath ());
	}

	private function runTry () {

		$openid = $this->getOpenIDUrl ();
		$consumer = $this->getConsumer ();

		$auth_request = $consumer->begin ($openid);

		// No auth request means we can't begin OpenID.
		if (!$auth_request) {
			displayError("Authentication error; not a valid OpenID.");
		}

		$sreg_request = \Auth_OpenID_SRegRequest::build(
		// Required
			array('nickname'),
			// Optional
			array('fullname', 'email'));

		if ($sreg_request) {
			$auth_request->addExtension($sreg_request);
		}

		$policy_uris = null;
		if (isset($_GET['policies'])) {
			$policy_uris = $_GET['policies'];
		}

		$pape_request = new \Auth_OpenID_PAPE_Request($policy_uris);
		if ($pape_request) {
			$auth_request->addExtension($pape_request);
		}

		// Redirect the user to the OpenID server for authentication.
		// Store the token for this authentication so we can verify the
		// response.

		// For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
		// form to send a POST request to the server.
		if ($auth_request->shouldSendRedirect()) {
			$redirect_url = $auth_request->redirectURL($this->getTrustRoot(), $this->getReturnTo());
			// If the redirect URL can't be built, display an error
			// message.
			if (\Auth_OpenID::isFailure($redirect_url)) {
				displayError("Could not redirect to server: " . $redirect_url->message);
			} else {
				// Send redirect.
				//header("Location: ".$redirect_url);
				return Response::redirect ($redirect_url);
			}
		} else {
			// Generate form markup and render it.
			$form_id = 'openid_message';
			$form_html = $auth_request->htmlMarkup($this->getTrustRoot(), $this->getReturnTo(),
				false, array('id' => $form_id));

			// Display an error if the form markup couldn't be generated;
			// otherwise, render the HTML.
			if (\Auth_OpenID::isFailure($form_html)) {
				displayError("Could not redirect to server: " . $form_html->message);
			} else {
				print $form_html;
			}
		}

		return null;

	}

	private function escape ($thing) {
		return htmlentities($thing);
	}

	private function runFinish () {
		$consumer = $this->getConsumer();

		// Complete the authentication process using the server's
		// response.
		$return_to = $this->getReturnTo();
		$response = $consumer->complete($return_to);

		// Check the response status.
		if ($response->status == Auth_OpenID_CANCEL) {
			// This means the authentication was cancelled.
			$msg = 'Verification cancelled.';
		} else if ($response->status == Auth_OpenID_FAILURE) {
			// Authentication failed; display the error message.
			$msg = "OpenID authentication failed: " . $response->message;
		} else if ($response->status == Auth_OpenID_SUCCESS) {

			// This means the authentication succeeded; extract the
			// identity URL and Simple Registration data (if it was
			// returned).
			$openid = $response->getDisplayIdentifier();
			return $this->afterLogin ($openid);
		}

		return Response::error ($msg);
	}

	protected function afterLogin ($openid) {

		$user = new DeligatedUser ();
		$user->setType ('openid');
		$user->setUniqueId ($openid);

		$this->setAdditionalParameters ($user);

		return $this->setDeligatedUser ($user);

	}

	protected function setAdditionalParameters (DeligatedUser $user) {

	}

	public function login ()
	{
		$finish = $this->request->input ('finish');
		if ($finish) {
			return $this->runFinish ();
		}
		else {
			return $this->runTry ();
		}
	}

	public abstract function getOpenIDUrl ();
}