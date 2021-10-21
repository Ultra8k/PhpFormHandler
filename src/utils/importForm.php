<?php
use Ultra8k\PHPFormUtilities\FormInitializer;

require(__DIR__ . '/../../vendor/autoload.php');

if (!session_id()) $form = new FormInitializer();

if(isset($_POST['submitted']))
{
  if (!$form->handler->SubmitForm()) {
    foreach ($form->handler->GetErrors() as $error) {
      $form->msg->error($error);
    }
  } else {
    $form->msg->success($form->config["FORM_SUCCESS"]);
  }
}