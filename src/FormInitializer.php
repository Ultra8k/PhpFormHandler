<?php
namespace Form\FormInitializer;

use Form\FormMiddleware\FormMiddleware;
use Form\FlasMessages\FormFlashMessages;
use CSRFP\Protector\csrfProtector;

require __DIR__ . "/../src/config/config.php";
require __DIR__ . '/../vendor/autoload.php';

if (!session_id()) @session_start();
csrfProtector::init();
$msg = new FormFlashMessages();

$form = new FormMiddleware($DOMAIN, $FROM, $TO, $INPUT_CLASS, $VALIDATION_CLASS);

if(isset($_POST['submitted']))
{
  if (!$form->SubmitForm()) {
    foreach ($form->GetErrors() as $error) {
      $msg->error($error);
    }
  } else {
    $msg->success($FORM_SUCCESS);
  }
}
