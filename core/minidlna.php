<?php

include_once "minidlna_status.php";

class minidlna {

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
        global $MiniDLNA_URL;
        $content = @file_get_contents($MiniDLNA_URL);
        
        if ($content === false) {
            throw new Exception("MiniDLNA status not accessible. Check for MiniDLNA was started and configured port was opened.");
        }
        
        $status = new minidlna_status();

        // Parsing - devi usare variabili temporanee
        $audioMatches = [];
        $videoMatches = [];
        $imageMatches = [];
    
        preg_match('/Audio files: (\d+)/', $content, $audioMatches);
        preg_match('/Video files: (\d+)/', $content, $videoMatches);
        preg_match('/Image files: (\d+)/', $content, $imageMatches);
        
        // Assegna i valori estratti
        $status->AUDIO = isset($audioMatches[1]) ? (int)$audioMatches[1] : 0;
        $status->VIDEO = isset($videoMatches[1]) ? (int)$videoMatches[1] : 0;
        $status->IMAGES = isset($imageMatches[1]) ? (int)$imageMatches[1] : 0;
                
        return $status;
    }







}