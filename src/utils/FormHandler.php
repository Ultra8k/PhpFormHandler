<?php
namespace Ultra8k\PHPFormUtilities;

use PHPMailer\PHPMailer\PHPMailer;
use Gregwar\Captcha as Captcha;

final class FormHandler {
	var array $recipients;
	var array $errors;
	var string $error_message;
	var string $name;
	var string $lname;
	var string $email;
	var string $phone;
	var string $message;
	var string $from_address;
	var string $form_random_key;
	var string $conditional_field;
	var array $arr_conditional_recipients;
	var array $fileupload_fields;
	// var $captcha_handler;
  var bool $isAjax;
  protected array $post;
  protected array $files;

	var $mailer;

	function __construct() {
		$this->recipients = array();
		$this->errors = array();
		$this->form_random_key = 'TQKW3DHCzBcyAH3';
		$this->conditional_field = '';
		$this->arr_conditional_recipients = array();
		$this->fileupload_fields = array();

		$this->mailer = new PHPMailer();
		$this->mailer->CharSet = 'utf-8';
	}

	// function EnableCaptcha($captcha_handler) {
	// 	$this->captcha_handler = $captcha_handler;
	// 	if (!session_id()) @session_start();
	// }

	function AddRecipient($email, $name = "") {
		$this->mailer->AddAddress($email, $name);
	}

	function SetFromAddress($from) {
		$this->from_address = $from;
	}

	function SetFormRandomKey($key) {
		$this->form_random_key = $key;
	}

	function GetSpamTrapInputName() {
		return 'sp'.md5('uAPvLXGjsK2CEMb'.$this->GetKey());
	}

	function SafeDisplay($value_name) {
		if (empty($this->post[$value_name])) {
			return '';
		}
		return htmlentities($this->post[$value_name]);
	}

	function GetFormIDInputName() {
		$rand = md5('sdqge8r0YGjCKae'.$this->GetKey());

		$rand = substr($rand, 0, 20);
		return 'id'.$rand;
	}


	function GetFormIDInputValue() {
		return md5('UYJuGo29Dll3L0f'.$this->GetKey());
	}

	function SetConditionalField($field) {
		$this->conditional_field = $field;
	}

	function AddConditionalRecipient($value, $email) {
		$this->arr_conditional_recipients[$value] = $email;
	}

	function AddFileUploadField($file_field_name, $accepted_types, $max_size) {

		$this->fileupload_fields[] =
			array("name" => $file_field_name,
				"file_types" => $accepted_types,
				"maxsize" => $max_size);
	}

	function ProcessForm($post, $files) {
    $this->post = $post;
    $this->files = $files;
		if (!isset($this->post['submitted'])) {
			$this->add_error("Submitted not set!");
			$this->error_message = implode(', ', $this->errors);
      return false;
		}
		if (!$this->Validate()) {
			$this->error_message = implode(', ', $this->errors);
      return false;
		}
		$this->CollectData();

		$ret = $this->SendFormSubmission();

		if (!$ret) {
			$this->error_message = implode(', ', $this->errors);
      if ($this->isAjax) {
        $this->sendAjaxResponse(false);
      }
		}

    if ($this->isAjax) $this->sendAjaxResponse(true);
		else return $ret;
	}

	function RedirectToURL($url) {
		header("Location: $url");
		exit;
	}

	function GetErrorMessage() {
		return $this->error_message;
	}

	function GetSelfScript() {
		return htmlentities($_SERVER['PHP_SELF']);
	}

	function GetName() {
		return $this->name;
	}

	function GetEmail() {
		return $this->email;
	}

	function GetMessage() {
		return htmlentities($this->message, ENT_QUOTES, "UTF-8");
	}

	/*--------  Private (Internal) Functions -------- */


	private function SendFormSubmission() {
		$this->CollectConditionalRecipients();

		$this->mailer->CharSet = 'utf-8';

		$this->mailer->Subject = "Contact form submission from $this->name";

		$this->mailer->From = $this->GetFromAddress();

		$this->mailer->FromName = $this->name;

		$this->mailer->AddReplyTo($this->email);

		$message = $this->ComposeFormtoEmail();

		$textMsg = trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s', '', $message)));
		$this->mailer->AltBody = @html_entity_decode($textMsg, ENT_QUOTES, "UTF-8");
		$this->mailer->MsgHTML($message);

		$this->AttachFiles();

		if (!$this->mailer->Send()) {
			$this->add_error("Failed to send email!");
      return false;
		}

    return true;
	}

  private function sendAjaxResponse($status) {
    $response = [];
    $response_code = $status ? 200 : 500;
    if ($status) {
      $response = [
        "status" => true,
        "message" => ['Message sent! Thanks for contacting us.']
      ];
    } else {
      $response = [
        "status" => false,
        "message" => $this->errors
      ];
    }
    header('Content-type:application/json;charset=utf-8');
    http_response_code($response_code);
    echo json_encode($response);
    exit();
  }

	private function CollectConditionalRecipients() {
		if (count($this->arr_conditional_recipients) > 0 &&
			!empty($this->conditional_field) &&
			!empty($this->post[$this->conditional_field])) {
			foreach($this->arr_conditional_recipients as $condn => $rec) {
				if (strcasecmp($condn, $this->post[$this->conditional_field]) == 0 &&
					!empty($rec)) {
					$this->AddRecipient($rec);
				}
			}
		}
	}

	/*
	Internal variables, that you donot want to appear in the email
	Add those variables in this array.
	*/
	private function IsInternalVariable($varname) {
		$arr_internal_vars = array('scaptcha',
			'submitted',
			$this->GetSpamTrapInputName(),
			$this->GetFormIDInputName()
		);
		if (in_array($varname, $arr_internal_vars)) {
			return true;
		}
		return false;
	}

	private function FormSubmissionToMail() {
		$ret_str = '';
		foreach($this->post as $key => $value) {
			if (!$this->IsInternalVariable($key)) {
				$value = htmlentities($value, ENT_QUOTES, "UTF-8");
				$value = nl2br($value);
				$key = ucfirst($key);
				$ret_str .= "<div class='label'>$key :</div><div class='value'>$value </div>\n";
			}
		}
		foreach($this->fileupload_fields as $upload_field) {
			$field_name = $upload_field["name"];
			if (!$this->IsFileUploaded($field_name)) {
				continue;
			}

			$filename = basename($this->files[$field_name]['name']);

			$ret_str .= "<div class='label'>File upload '$field_name' :</div><div class='value'>$filename </div>\n";
		}
		return $ret_str;
	}

	private function ExtraInfoToMail() {
		$ret_str = '';

		$ip = $_SERVER['REMOTE_ADDR'];
		$ret_str = "<div class='label'>IP address of the submitter:</div><div class='value'>$ip</div>\n";

		return $ret_str;
	}

	private function GetMailStyle() {
		$retstr = "\n<style>".
		"body,.label,.value { font-family:Arial,Verdana; } ".
		".label {font-weight:bold; margin-top:5px; font-size:1em; color:#333;} ".
		".value {margin-bottom:15px;font-size:0.8em;padding-left:5px;} ".
		"</style>\n";

		return $retstr;
	}

	private function GetHTMLHeaderPart() {
		$retstr = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">'.
		"\n".
		'<html><head><title></title>'.
		'<meta http-equiv=Content-Type content="text/html; charset=utf-8">';
		$retstr .= $this->GetMailStyle();
		$retstr .= '</head><body>';
		return $retstr;
	}

	private function GetHTMLFooterPart() {
		$retstr = '</body></html>';
		return $retstr;
	}

	private function ComposeFormtoEmail() {
		$header = $this->GetHTMLHeaderPart();
		$formsubmission = $this->FormSubmissionToMail();
		// $extra_info = $this->ExtraInfoToMail();
		$footer = $this->GetHTMLFooterPart();

		$message = $header.
		"Submission from 'contact us' form:<p>$formsubmission</p><hr/>".$footer;

		return $message;
	}

	private function AttachFiles() {
		foreach($this->fileupload_fields as $upld_field) {
			$field_name = $upld_field["name"];
			if (!$this->IsFileUploaded($field_name)) {
				continue;
			}

			$filename = basename($this->files[$field_name]['name']);

			$this->mailer->AddAttachment($this->files[$field_name]["tmp_name"], $filename);
		}
	}

	private function GetFromAddress() {
		if (!empty($this->from_address)) {
			return $this->from_address;
		}

		$host = $_SERVER['SERVER_NAME'];

		$from = "nobody@$host";
		return $from;
	}

	private function Validate() {
		$ret = true;
		//security validations
		if (empty($this->post[$this->GetFormIDInputName()]) ||
			$this->post[$this->GetFormIDInputName()] != $this->GetFormIDInputValue()) {
			//The proper error is not given intentionally
			$this->add_error("Automated submission prevention: case 1 failed");
			$ret = false;
		}

		//This is a hidden input field. Humans won't fill this field.
		if (!empty($this->post[$this->GetSpamTrapInputName()])) {
			//The proper error is not given intentionally
			$this->add_error("Automated submission prevention: case 2 failed");
			$ret = false;
		}

		// name validations
		if (empty($this->post['name'])) {
			$this->add_error("Please provide your name");
			$ret = false;
		} else
		if (strlen($this->post['name']) > 75) {
			$this->add_error("Name is too long!");
			$ret = false;
		}

		//email validations
		if (empty($this->post['email'])) {
			$this->add_error("Please provide your email address");
			$ret = false;
		} else
		if (strlen($this->post['email']) > 50) {
			$this->add_error("Email address is too long!");
			$ret = false;
		} else
		if (!$this->validate_email($this->post['email'])) {
			$this->add_error("Please provide a valid email address");
			$ret = false;
		}

		// phone validation
		if (empty($this->post['phone'])) {
			$this->add_error("Please provide your phone number");
			$ret = false;
		} else
		if (!preg_match('/^\([0-9]{3}\)[\s\+]{1}[0-9]{3}-[0-9]{4}+$/', $this->post['phone'])) {
			$this->add_error("Phone enter a valid phone number");
			$ret = false;
		}

		//message validaions
		if (strlen($this->post['message']) > 500) {
			$this->add_error("Message is too long");
			$ret = false;
		}

		// captcha validaions
		if (isset($_SESSION['captcha'])) {
			if (isset($this->post['captcha'])) {
				if (!Captcha\PhraseBuilder::comparePhrases($_SESSION['captcha'], $_POST['captcha'])) {
					$this->add_error("Captcha is not correct!");
					$ret = false;
				}
			} else {
				$this->add_error("Captcha is missing!");
				$ret = false;
			}
		}

		//file upload validations
		if (!empty($this->fileupload_fields)) {
			if (!$this->ValidateFileUploads()) {
				$ret = false;
			}
		}
		return $ret;
	}

	private function ValidateFileType($field_name, $valid_filetypes) {
		$ret = true;
		$info = pathinfo($this->files[$field_name]['name']);
		$extn = $info['extension'];
		$extn = strtolower($extn);

		$arr_valid_filetypes = explode(',', $valid_filetypes);
		if (!in_array($extn, $arr_valid_filetypes)) {
			$this->add_error("Valid file types are: $valid_filetypes");
			$ret = false;
		}
		return $ret;
	}

	private function ValidateFileSize($field_name, $max_size) {
		$size_of_uploaded_file =
			$this->files[$field_name]["size"] / 1024; //size in KBs
		if ($size_of_uploaded_file > $max_size) {
			$this->add_error("The file is too big. File size should be less than $max_size KB");
			return false;
		}
		return true;
	}

	private function IsFileUploaded($field_name) {
		if (empty($this->files[$field_name]['name'])) {
			return false;
		}
		if (!is_uploaded_file($this->files[$field_name]['tmp_name'])) {
			return false;
		}
		return true;
	}

	private function ValidateFileUploads() {
		$ret = true;
		foreach($this->fileupload_fields as $upld_field) {
			$field_name = $upld_field["name"];

			$valid_filetypes = $upld_field["file_types"];

			if (!$this->IsFileUploaded($field_name)) {
				continue;
			}

			if ($this->files[$field_name]["error"] != 0) {
				$this->add_error("Error in file upload; Error code:".$this->files[$field_name]["error"]);
				$ret = false;
			}

			if (!empty($valid_filetypes) &&
				!$this->ValidateFileType($field_name, $valid_filetypes)) {
				$ret = false;
			}

			if (!empty($upld_field["maxsize"]) &&
				$upld_field["maxsize"] > 0) {
				if (!$this->ValidateFileSize($field_name, $upld_field["maxsize"])) {
					$ret = false;
				}
			}

		}
		return $ret;
	}

	private function StripSlashes($str) {
		$str = stripslashes($str);
		
		return $str;
	}
	/*
	Sanitize() function removes any potential threat from the
	data submitted. Prevents email injections or any other hacker attempts.
	if $remove_nl is true, newline characters are removed from the input.
	*/
	private function Sanitize($str, $remove_nl = true) {
		$str = $this->StripSlashes($str);

		if ($remove_nl) {
			$injections = array('/(\n+)/i',
				'/(\r+)/i',
				'/(\t+)/i',
				'/(%0A+)/i',
				'/(%0D+)/i',
				'/(%08+)/i',
				'/(%09+)/i'
			);
			$str = preg_replace($injections, '', $str);
		}

		return $str;
	}

	/*Collects clean data from the $this->post array and keeps in internal variables.*/
	private function CollectData() {
    $this->isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
		$this->name = $this->Sanitize($this->post['name']);
		$this->email = $this->Sanitize($this->post['email']);
		$this->phone = $this->Sanitize($this->post['phone']);

		/*newline is OK in the message.*/
		$this->message = $this->StripSlashes($this->post['message']);
	}

	private function add_error($error) {
		array_push($this->errors, $error);
	}

	private function validate_email($email) {
		return preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/", $email);
	}

	private function GetKey() {
		return $this->form_random_key.$_SERVER['SERVER_NAME'].$_SERVER['REMOTE_ADDR'];
	}

}
