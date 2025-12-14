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
        $command = 'sudo /usr/bin/systemctl restart minidlna.service';
        
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
        
        // cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $MiniDLNA_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $content = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($content === false || $httpCode !== 200) {
            throw new Exception("MiniDLNA status not accessible on $MiniDLNA_URL. HTTP Code: $httpCode, Error: $error");
        }
        
        $status = new minidlna_status();

        $audioMatches = [];
        $videoMatches = [];
        $imageMatches = [];

        $audioResult = preg_match('/<td>Audio files<\/td>\s*<td>(\d+)<\/td>/', $content, $audioMatches);
        $videoResult = preg_match('/<td>Video files<\/td>\s*<td>(\d+)<\/td>/', $content, $videoMatches);
        $imageResult = preg_match('/<td>Image files<\/td>\s*<td>(\d+)<\/td>/', $content, $imageMatches);

        $status->AUDIO = isset($audioMatches[1]) ? (int)$audioMatches[1] : 0;
        $status->VIDEO = isset($videoMatches[1]) ? (int)$videoMatches[1] : 0;
        $status->IMAGES = isset($imageMatches[1]) ? (int)$imageMatches[1] : 0;
        
        return $status;
    }







}