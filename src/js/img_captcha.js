document.forms['contactus'].scaptcha.validator = new CaptchaValidator(
  document.forms['contactus'].scaptcha,
  document.images['scaptcha_img']
);

function SCaptchaValidate() {
  return document.forms['contactus'].scaptcha.validator.validate();
}

function refresh_captcha_img() {
  var img = document.images['scaptcha_img'];
  img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
}