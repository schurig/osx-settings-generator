<?php

class OSXSettings {

  public function __construct() {

  }

  public function getLinies() {
    $userSettings = [];

    $handle = fopen("commands", "r");
    if ($handle) {
      while (($command = fgets($handle)) !== false) {
        $outcome = false; // reset outcome
        $checkCommand = $this->getCheckCommand($command);

        exec($checkCommand, $outcome, $status);
        $lookingFor = '/does not exist/';

        if(!empty($outcome)) {
          if(!preg_match($lookingFor, $outcome[0], $goal, PREG_OFFSET_CAPTURE, 3)) {
            $type = $this->getFileType($this->getCheckCommand($command, true));
            $newCommand = str_replace('%operation%', 'write', $command);
            $newCommand = str_replace('%type%', $type, $newCommand);
            if($type === 'string') {
              $newCommand = str_replace('%value%', "\"" . $outcome[0] . "\"", $newCommand);
            } else {
              $newCommand = str_replace('%value%', $outcome[0], $newCommand);
            }

            $userSettings[] = $newCommand;
          }
        }

      }
    } else {
      echo 'error';
    }
    fclose($handle);

    return $userSettings;
  }

  public function writeConfigFile($commands) {
    $commandsNewLine = implode("\n", $commands);

    $file = 'userSettings';
    // Schreibt den Inhalt in die Datei zurück
    if(file_put_contents($file, $commandsNewLine)) {
      return true;
    }

    return false;
  }

  private function getCheckCommand($command, $checkType = false) {

    if($checkType) {
      $checkCommand = str_replace('%operation%', 'read-type', $command);
    } else {
      $checkCommand = str_replace('%operation%', 'read', $command);
    }
    $checkCommand = str_replace(' -%type%', '', $checkCommand);
    $checkCommand = str_replace(' %value%', '', $checkCommand);

    return $checkCommand;
  }

  private function getFileType($command) {
    $type = false;

    exec($command, $outcome, $status);

    $outcome = $outcome[0];
    if($outcome === "Type is boolean") {
      $type = "bool";
    } else if($outcome === "Type is string") {
      $type = "sting";
    } else {
      $type = "DONT#KNOW!!!!";
    }

    return $type;
  }

}

$osxSettings = new OSXSettings();
$userSettings = $osxSettings->getLinies();
$res = $osxSettings->writeConfigFile($userSettings);

?>