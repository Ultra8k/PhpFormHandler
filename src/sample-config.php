<?php

return array(
  "DOMAIN" => "localhost",
  "BASE_URL" => "http://localhost:8888",

  // csrf variables
  "VERIFY_GET" => array("*://*/csrfp/*", "*://*/config/*"),
  
  // Form Variables
  "FROM_EMAIL" => "email@email.com",
  "TO_EMAIL" => ["email@email.com"],
  "INPUT_CLASS" => "",
  "VALIDATION_CLASS" => "",
  
  // Flash Messages
  "FORM_SUCCESS" => "Your info was successfully sent.",
);