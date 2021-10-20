<?php
namespace Form\FlasMessages;

use Plasticbrain\FlashMessages\FlashMessages;

class FormFlashMessages extends FlashMessages {
    public function getMessages($types=self::defaultType, $clear=false) {
        $output = [];
        
        // Print all the message types
        if (is_null($types) || !$types || (is_array($types) && empty($types)) ) {
          $types = array_keys($this->msgTypes);
        
        // Print multiple message types (as defined by an array)
        } elseif (is_array($types) && !empty($types)) {
          $theTypes = $types;
          $types = [];
          foreach($theTypes as $type) {
            $types[] = strtolower($type[0]);
          }
      
        // Print only a single message type
        } else {
          $types = [strtolower($types[0])];
        }
        
        
        // Retrieve and format the messages, then remove them from session data
        foreach ($types as $type) {
          if (!isset($_SESSION['flash_messages'][$type]) || empty($_SESSION['flash_messages'][$type]) ) continue;
          foreach( $_SESSION['flash_messages'][$type] as $msgData ) {
            array_push($output, $msgData['message']);
          }
          if ($clear) $this->clear($type);
        }
        
        return $output;
      }
}