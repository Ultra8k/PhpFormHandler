<?php
namespace Ultra8k\PHPFormUtilities;

use Ultra8k\PHPFormUtilities\FormMiddleware;
use OwaspCsrfProtection\CsrfProtector;
use Ultra8k\PHPFormUtilities\FlashMessages;

class FormInitializer {
  public FlashMessages $msg;
  public $handler;
  
  function __construct()
  {
    $this->config = include(__DIR__ . "/../config/config.php");
    if (!session_id()) @session_start();
    CsrfProtector::init();
    $this->msg = new FlashMessages();
    $this->handler = new FormMiddleware($this->config["DOMAIN"], $this->config["FROM"], $this->config["TO"], $this->config["INPUT_CLASS"], $this->config["VALIDATION_CLASS"]);  
  }

  public function SubmitForm()
  {
    if(isset($_POST['submitted']))
    {
      if (!$this->handler->SubmitForm()) {
        foreach ($this->handler->GetErrors() as $error) {
          $this->msg->error($error);
        }
      } else {
        $this->msg->success($this->config["FORM_SUCCESS"]);
      }
    }
  }
}
