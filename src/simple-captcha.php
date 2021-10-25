<?php
require_once(__DIR__ . "/utils/importForm.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>
  <div id="contact-cta" class="container px-4 py-6 flex align-center justify-center">
    <div class="w-full">
      <ul>
      <?php if ($form->msg->hasErrors()) {
        foreach ($form->msg->getMessages("error") as $message) {
          echo "<li>$message</li>";
        }
      }
      ?>
      </ul>
      <form id="contactus" class="mt-4" action="<?php echo $form->form_mw->GetFormAction(); ?>" method="post" accept-charset="UTF-8">
        <?php echo $form->form_mw->SpamField(); ?>
        <div class="flex flex-wrap">
          <div class="w-full md:w-1/2 px-4">
            <div class="mb-4">
              <label for="name" class="block mb-2 text-sm text-gray-50">Your Full Name</label>

              <!-- name field -->
              <?php echo $form->form_mw->NameField(); ?>
            </div>
            <div class="mb-4">
              <label for="email" class="block mb-2 text-sm text-gray-50">Your Email</label>

              <!-- email field -->
              <?php echo $form->form_mw->EmailField(); ?>
            </div>
            <div class="mb-4">
              <label for="phone" class="block mb-2 text-sm text-gray-50">Your Phone Number</label>

              <!-- phone field -->
              <?php echo $form->form_mw->PhoneField(); ?>
            </div>
          </div>
          <div class="w-full md:w-1/2 px-4">
            <div class="mb-4">
              <label for="message" class="flex justify-between mb-2 text-sm text-gray-50">Your Message <span
                  class="text-gray-300 inline-block" id="character-count"><span
                    id="character-counter">0</span>/500</span></label>

              <!-- message field -->
              <?php echo $form->form_mw->MessageField(); ?>
            </div>
            <?php echo $form->form_mw->SimpleCaptchaField(); ?>
          </div>
          <div class="w-full flex justify-end p-4">
            <button type="submit" name="submitForm" id="submitForm"
              class="inline-flex justify-center py-3 px-6 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-secondary-base hover:bg-secondary-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary-light">Submit</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</body>

</html>