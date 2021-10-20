<?php
namespace Form\FormMiddleware;

use Form\FormHandler\FormHandler;

class FormMiddleware {

  protected FormHandler $form;
  protected string $from;
  protected array $recipients;
  public string|null $class = null;
  public string|null $validationClass = null;
  
  public function __construct(string $site, string $from, array $to, string $class = null, string $validationClass = null)
  {
    $this->form = new FormHandler();
    $this->form->SetFormRandomKey(base64_encode($site));
    $this->from = $from;
    $this->SetFromAddress();
    $this->recipients = $to;
    $this->AddRecipients();
    if ($class) $this->SetInputClass($class);
    if ($validationClass) $this->SetValidationClass($validationClass);
  }

  private function SetFromAddress() {
    $this->form->SetFromAddress($this->from);
  }

  private function AddRecipients() {
    foreach ($this->recipients as $recipient) {
      $this->form->AddRecipient($recipient);
    }
  }

  public function SetInputClass(string $class) {
    $this->class = $class;
  }

  public function SetValidationClass(string $class) {
    $this->validationClass = $class;
  }

  private function GetInputValidationClass() {
    $class = "";
    if ($this->validationClass) $class .= $this->validationClass;
    if ($this->validationClass && $this->class) $class .= ' ';
    if ($this->class) $class .= $this->class;

    return $class;
  }

  public function GetFormAction() {
    return $this->form->GetSelfScript();
  }

  public function SubmitForm() {
    if(!$this->form->ProcessForm($_POST, $_FILES))
    {
      return false;
    }
    return true;
  }

  public function GetErrors() {
    return $this->form->errors;
  }

  public function SpamField()
  {
    return '
        <input type="hidden" name="submitted" id="submitted" value="1" />
        <input type="hidden" name="' . $this->form->GetFormIDInputName() . '" value="' . $this->form->GetFormIDInputValue() . '" />
        <input type="text" class="spmhidip" name="' . $this->form->GetSpamTrapInputName() . '" />
    ';
  }

  public function NameField() {
      return '
        <input type="text" name="name" id="name" class="' . $this->GetInputValidationClass() . '" value="' . $this->form->SafeDisplay('name') . '" placeholder="John Doe" pattern="^[a-zA-Z\s]+$">
      ';
  }

  public function EmailField() {
      return '
        <input type="text" name="email" id="email" class="' . $this->GetInputValidationClass() . '" value="' . $this->form->SafeDisplay('email') . '" placeholder="John_Doe@email.com" pattern="^[a-zA-Z0-9.!#$%&\'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)+$">
      ';
  }

  public function PhoneField() {
      return '
        <input type="text" name="phone" id="phone" class="' . $this->GetInputValidationClass() . '" max-length="12" value="' . $this->form->SafeDisplay('phone') . '" placeholder="(123) 456-7890" pattern="^\(\d{3}\)\s\d{3}-\d{4}$">
      ';
  }

  public function MessageField() {
      return '
        <textarea name="message" id="message" class="' . $this->class . '" maxlength="500 cols="30" rows="8">' .  $this->form->SafeDisplay('message') . '</textarea>
      ';
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