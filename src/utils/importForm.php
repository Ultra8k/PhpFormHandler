<?php
use Ultra8k\PHPFormUtilities\FormMiddleware;

require(__DIR__ . '/../../vendor/autoload.php');

$has_captcha = preg_match('/captcha/', $_SERVER['REQUEST_URI']);

if (!session_id()) $form = new FormMiddleware(false, $has_captcha);

if(isset($_POST['submitted']))
{
  $form->SubmitForm();
}