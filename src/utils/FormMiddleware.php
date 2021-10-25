<?php
namespace Ultra8k\PHPFormUtilities;

use OwaspCsrfProtection\CsrfProtector;
use Ultra8k\PHPFormUtilities\FlashMessages;
use Ultra8k\PHPFormUtilities\FormHandler;
use Gregwar\Captcha as Captcha;

class FormMiddleware {
  private $config;
  public FlashMessages $msg;
  protected FormHandler $handler;
  protected string $from;
  protected array $recipients;
  public ?string $input_class = null;
  public ?string $validation_class = null;
  protected bool $jsValidation;
  protected Captcha\CaptchaBuilder $captcha;
  
  public function __construct(bool $jsValidation = false)
  {
    $this->config = include(__DIR__ . "/../config/config.php");

    CsrfProtector::init();

    $this->msg = new FlashMessages();
    $this->handler = new FormHandler();
    $this->handler->SetFormRandomKey(base64_encode($this->config["DOMAIN"]));
    $this->from = $this->config["FROM_EMAIL"];
    $this->SetFromAddress();
    $this->recipients = $this->config["TO_EMAIL"];
    $this->AddRecipients();
    if ($this->config["INPUT_CLASS"]) $this->SetInputClass($this->config["INPUT_CLASS"]);
    if ($this->config["VALIDATION_CLASS"]) $this->SetValidationClass($this->config["VALIDATION_CLASS"]);
    $this->jsValidation = $jsValidation;

    // if ($captcha) {
    //   $this->captcha = new Captcha\CaptchaBuilder;
    //   $this->RefreshCaptcha();
    // }
  }

  private function SetFromAddress() {
    $this->handler->SetFromAddress($this->from);
  }

  private function AddRecipients() {
    foreach ($this->recipients as $recipient) {
      $this->handler->AddRecipient($recipient);
    }
  }

  public function SetInputClass(string $class) {
    $this->input_class = $class;
  }

  private function GetInputClass() {
    return $this->input_class || "";
  }

  public function SetValidationClass(string $class) {
    $this->validation_class = $class;
  }

  private function GetInputValidationClass() {
    $class = "";
    if ($this->validation_class) $class .= $this->validation_class;
    if ($this->validation_class && $this->input_class) $class .= ' ';
    $class .= $this->GetInputClass();

    return $class;
  }

  public function GetFormAction() {
    return $this->handler->GetSelfScript();
  }

  // private function RefreshCaptcha() {
  //   unset($_SESSION['captcha']);
  //   $this->captcha->build();
  //   $_SESSION['captcha'] = $this->captcha->getPhrase();
  // }

  // public function TestCaptcha() {
  //   if (
  //     isset($_SESSION['captcha']) &&
  //     Captcha\PhraseBuilder::comparePhrases($_SESSION['captcha'], $_POST['captcha'])
  //   ) {
  //     return true;
  //   } else {
  //     return false;
  //   }
  // }

  public function SubmitForm() {
    if (!$this->handler->ProcessForm($_POST, $_FILES)) {
      foreach ($this->GetErrors() as $error) {
        $this->msg->error($error);
      }
    } else {
      $this->msg->success($this->config["FORM_SUCCESS"]);
    }
  }

  public function GetErrors() {
    return $this->handler->errors;
  }

  public function SpamField()
  {
    return '
        <input type="hidden" name="submitted" id="submitted" value="1" />
        <input type="hidden" name="' . $this->handler->GetFormIDInputName() . '" value="' . $this->handler->GetFormIDInputValue() . '" />
        <input type="text" class="spmhidip" name="' . $this->handler->GetSpamTrapInputName() . '" />
    ';
  }

  public function NameField() {
      $input = '
        <input type="text" name="name" id="name" class="' . $this->GetInputValidationClass() . '" value="' . $this->handler->SafeDisplay('name') . '" placeholder="John Doe" pattern="^[a-zA-Z\s]+$">
      ';
      if ($this->jsValidation) $input .= '<span id="contactus_name_errorloc" class="error"></span>';
      return $input;
  }

  public function EmailField() {
      $input = '
        <input type="text" name="email" id="email" class="' . $this->GetInputValidationClass() . '" value="' . $this->handler->SafeDisplay('email') . '" placeholder="John_Doe@email.com" pattern="^[a-zA-Z0-9.!#$%&\'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)+$">
      ';
      if ($this->jsValidation) $input .= '<span id="contactus_email_errorloc" class="error"></span>';
      return $input;
  }

  public function PhoneField() {
      $input = '
        <input type="text" name="phone" id="phone" class="' . $this->GetInputValidationClass() . '" max-length="12" value="' . $this->handler->SafeDisplay('phone') . '" placeholder="(123) 456-7890" pattern="^\(\d{3}\)\s\d{3}-\d{4}$">
      ';
      if ($this->jsValidation) $input .= '<span id="contactus_phone_errorloc" class="error"></span>';
      return $input;
  }

  public function MessageField() {
      $input = '
        <textarea name="message" id="message" class="' . $this->GetInputClass() . '" maxlength="500 cols="30" rows="8">' .  $this->handler->SafeDisplay('message') . '</textarea>
      ';
      if ($this->jsValidation) $input .= '<span id="contactus_message_errorloc" class="error"></span>';
      return $input;
  }

  public function ImageCaptchaField() {
    $input = '
    <div><img alt="Captcha image" src="./includes/captcha/CreateCaptcha.php" id="captcha_img" /></div>
    <label for="captcha" >Enter the code above here:</label>
    <input type="text" name="captcha" id="captcha" maxlength="10" />  
    ';
    if ($this->jsValidation) $input .= '<span id="contactus_scaptcha_errorloc" class="error"></span>';
    return $input;
  }

}