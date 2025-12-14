<?php

include_once "minidlna_status.php";

class minidlna {

    // constant
    private static String $StatusUrl = 'http://localhost:8200';

    /**
     * Perform database rebuild and update contents
     *
     * @return void
     */
    public static function RebuildMiniDLNA() {
        // Command to rebuild
        $command = 'sudo /usr/sbin/service minidlna force-reload';
        
        // fix command
        $escapedCommand = escapeshellcmd($command);
        
        // Exec and get output
        $output = [];
        $returnVar = 0;
        exec($escapedCommand, $output, $returnVar);
        
        if ($returnVar === 0) {
            return "Rebuild started with success. Output: " . implode("\n", $output);
        } else {
            return "Error during rebuild. Code: $returnVar. Output: " . implode("\n", $output);
        }
    }


    /**
     * Get MiniDLNA status
     *
     * @return minidlna_status
     */
    public static function GetMiniDLNAStatus() {
        
        $content = @file_get_contents(self::$StatusUrl);
        
        if ($content === false) {
            throw new Exception("MiniDLNA status not accessible. Check for MiniDLNA was started and configured port was opened.");
        }
        
        $status = new minidlna_status();

        // Parsing 
        preg_match('/Audio files: (\d+)/', $content, $status->AUDIO);
        preg_match('/Video files: (\d+)/', $content, $status->VIDEO);
        preg_match('/Image files: (\d+)/', $content, $status->IMAGES);
                
        return $status;
    }







}