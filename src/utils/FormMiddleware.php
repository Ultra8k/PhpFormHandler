<?php
namespace Ultra8k\PHPFormUtilities;

use Ultra8k\PHPFormUtilities\FormHandler;

class FormMiddleware {

  protected FormHandler $form_handler;
  protected string $from;
  protected array $recipients;
  public ?string $input_class = null;
  public ?string $validation_class = null;
  
  public function __construct(string $site, string $from, array $to, ?string $class = null, ?string $validationClass = null)
  {
    $this->form_handler = new FormHandler();
    $this->form_handler->SetFormRandomKey(base64_encode($site));
    $this->from = $from;
    $this->SetFromAddress();
    $this->recipients = $to;
    $this->AddRecipients();
    if ($class) $this->SetInputClass($class);
    if ($validationClass) $this->SetValidationClass($validationClass);
  }

  private function SetFromAddress() {
    $this->form_handler->SetFromAddress($this->from);
  }

  private function AddRecipients() {
    foreach ($this->recipients as $recipient) {
      $this->form_handler->AddRecipient($recipient);
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
    if ($this->input_class) $class .= $this->input_class;

    return $class;
  }

  public function GetFormAction() {
    return $this->form_handler->GetSelfScript();
  }

  public function SubmitForm() {
    if(!$this->form_handler->ProcessForm($_POST, $_FILES))
    {
      return false;
    }
    return true;
  }

  public function GetErrors() {
    return $this->form_handler->errors;
  }

  public function SpamField()
  {
    return '
        <input type="hidden" name="submitted" id="submitted" value="1" />
        <input type="hidden" name="' . $this->form_handler->GetFormIDInputName() . '" value="' . $this->form_handler->GetFormIDInputValue() . '" />
        <input type="text" class="spmhidip" name="' . $this->form_handler->GetSpamTrapInputName() . '" />
    ';
  }

  public function NameField($withJsValidation = false) {
      $input = '
        <input type="text" name="name" id="name" class="' . $this->GetInputValidationClass() . '" value="' . $this->form_handler->SafeDisplay('name') . '" placeholder="John Doe" pattern="^[a-zA-Z\s]+$">
      ';
      if ($withJsValidation) $input .= '<span id="contactus_name_errorloc" class="error"></span>';
      return $input;
  }

  public function EmailField($withJsValidation = false) {
      $input = '
        <input type="text" name="email" id="email" class="' . $this->GetInputValidationClass() . '" value="' . $this->form_handler->SafeDisplay('email') . '" placeholder="John_Doe@email.com" pattern="^[a-zA-Z0-9.!#$%&\'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)+$">
      ';
      if ($withJsValidation) $input .= '<span id="contactus_email_errorloc" class="error"></span>';
      return $input;
  }

  public function PhoneField($withJsValidation = false) {
      $input = '
        <input type="text" name="phone" id="phone" class="' . $this->GetInputValidationClass() . '" max-length="12" value="' . $this->form_handler->SafeDisplay('phone') . '" placeholder="(123) 456-7890" pattern="^\(\d{3}\)\s\d{3}-\d{4}$">
      ';
      if ($withJsValidation) $input .= '<span id="contactus_phone_errorloc" class="error"></span>';
      return $input;
  }

  public function MessageField($withJsValidation = false) {
      $input = '
        <textarea name="message" id="message" class="' . $this->GetInputClass() . '" maxlength="500 cols="30" rows="8">' .  $this->form_handler->SafeDisplay('message') . '</textarea>
      ';
      if ($withJsValidation) $input .= '<span id="contactus_message_errorloc" class="error"></span>';
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