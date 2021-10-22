<?php
use Ultra8k\PHPFormUtilities\Captcha\ImageCaptcha;
require(__DIR__ . '/../../../vendor/autoload.php');

if(isset($_POST['validate_captcha']))
{
    $captchaobj = new ImageCaptcha('scaptcha');
    header("pragma: no-cache");
    header("cache-control: no-cache");

    if(!$captchaobj->ValidateCaptcha($_POST['scaptcha']))
    {
        echo "The code does not match. Please try again!";
    }
    else
    {
        echo "success";
    }
}
else
{
    $captcha = new ImageCaptcha('scaptcha');
    header("pragma: no-cache");
    header("cache-control: no-cache");
    $captcha->DisplayCaptcha();
}
?>