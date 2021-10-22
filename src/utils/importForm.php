<?php
use Ultra8k\PHPFormUtilities\FormMiddleware;

require(__DIR__ . '/../../vendor/autoload.php');
if (!session_id()) @session_start();
if (!isset($form)) $form = new FormMiddleware;

if(isset($_POST['submitted']))
{
  $form->SubmitForm();
}