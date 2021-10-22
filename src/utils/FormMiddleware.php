<?php
namespace Ultra8k\PHPFormUtilities;

use Ultra8k\PHPFormUtilities\FormHandler;
use Ultra8k\PHPFormUtilities\Captcha as Captcha;

class FormMiddleware {

  protected FormHandler $handler;
  protected string $from;
  protected array $recipients;
  public ?string $input_class = null;
  public ?string $validation_class = null;
  protected $captcha;
  protected bool $jsValidation;
  
  public function __construct(
    string $site,
    string $from,
    array $to,
    ?string $class = null,
    ?string $validationClass = null,
    bool $jsValidation,
    ?string $captcha = null)
  {
    $this->handler = new FormHandler();
    $this->handler->SetFormRandomKey(base64_encode($site));
    $this->from = $from;
    $this->SetFromAddress();
    $this->recipients = $to;
    $this->AddRecipients();
    if ($class) $this->SetInputClass($class);
    if ($validationClass) $this->SetValidationClass($validationClass);
    $this->jsValidation = $jsValidation;

    if ($captcha === "simple") $this->captcha = new Captcha\SimpleCaptcha('scaptcha');
    if ($captcha === "image") $this->captcha = new Captcha\ImageCaptcha('scaptcha');

    if ($this->captcha) $this->handler->EnableCaptcha($this->captcha);
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

  public function SubmitForm() {
    if(!$this->handler->ProcessForm($_POST, $_FILES))
    {
      return false;
    }
    return true;
  }

  private function GenerateImageCaptcha() {
    header("pragma: no-cache");
    header("cache-control: no-cache");
    $this->captcha->DisplayCaptcha();
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

  public function SimpleCaptchaField() {
    $input = '
    <label for="scaptcha">' . $this->captcha->GetSimpleCaptcha() . '</label>
    <input type="text" name="scaptcha" id="scaptcha" maxlength="10" /><br/>
    ';
    if ($this->jsValidation) $input .= '<span id="contactus_scaptcha_errorloc" class="error"></span>';
    return $input;
  }

  public function ImageCaptchaRefresh() {
    return '
    <div class="short_explanation">Can"t read the image?
    <a href="javascript: refresh_captcha_img();">Click here to refresh</a>.</div>
    ';
  }

  public function ImageCaptchaField() {
    $input = '
    <div><img alt="Captcha image" src="./includes/captcha/GenerateCaptcha.php?rand=1" id="scaptcha_img" /></div>
    <label for="scaptcha" >Enter the code above here:</label>
    <input type="text" name="scaptcha" id="scaptcha" maxlength="10" />  
    ';
    if ($this->jsValidation) $input .= '<span id="contactus_scaptcha_errorloc" class="error"></span>';
    return $input;
  }

  public function FormInitAjaxResponse() {
    $response = [
      "spam_field" => $this->SpamField(),
      "name_field" => $this->NameField(),
      "email_field" => $this->EmailField(),
      "phone_field" => $this->PhoneField(),
      "message_field" => $this->MessageField()
    ];

    header('Content-type:application/json;charset=utf-8');
    http_response_code(200);
    echo json_encode($response);
    exit();
  }

}