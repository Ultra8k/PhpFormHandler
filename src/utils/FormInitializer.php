<?php
namespace Ultra8k\PHPFormUtilities;

use Ultra8k\PHPFormUtilities\FormMiddleware;
use OwaspCsrfProtection\CsrfProtector;
use Ultra8k\PHPFormUtilities\FlashMessages;

class FormInitializer {
  public FlashMessages $msg;
  public $form_mw;
  protected ?string $captcha_font = null;
  
  function __construct(?string $captcha = null)
  {
    $this->config = include(__DIR__ . "/../config/config.php");

    if (!session_id()) @session_start();

    CsrfProtector::init();

    $this->msg = new FlashMessages();
    
    $this->form_mw = new FormMiddleware(
      $this->config["DOMAIN"],
      $this->config["FROM"],
      $this->config["TO"],
      $this->config["INPUT_CLASS"],
      $this->config["VALIDATION_CLASS"],
      $jsValidation = false,
      $captcha
    );
  }
}
