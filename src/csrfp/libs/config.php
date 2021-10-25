<?php
$config = include(__DIR__ . "/../../config/config.php");
/**
 * Configuration file for CSRF Protector
 * Necessary configurations are (library would throw exception otherwise)
 * ---- failedAuthAction
 * ---- jsUrl
 * ---- tokenLength
 */
return array(
  "CSRFP_TOKEN" => "",
	"failedAuthAction" => array(
		"GET" => 0,
		"POST" => 0),
	"errorRedirectionPage" => $config["BASE_URL"] . "/error.php",
	"customErrorMessage" => "",
  "logDirectory" => "../../log/",
  "jsPath" => "../../js/csrfprotector.js",
	"jsUrl" => $config["BASE_URL"] . "/csrfp/js/csrfprotector.js",
	"tokenLength" => 256,
	"cookieConfig" => array(
		"path" => '/',
		"domain" => $config["DOMAIN"],
		"secure" => false,
		"expire" => 0,
    "httponly" => false,
    "samesite" => 'Lax'
	),
	"disabledJavascriptMessage" => "This site attempts to protect users against <a href=\"https://www.owasp.org/index.php/Cross-Site_Request_Forgery_%28CSRF%29\">
	Cross-Site Request Forgeries </a> attacks. In order to do so, you must have JavaScript enabled in your web browser otherwise this site will fail to work correctly for you.
	 See details of your web browser for how to enable JavaScript.",
	"verifyGetFor" => $config["VERIFY_GET"]
);