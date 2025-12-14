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

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($content);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Usa XPath per estrarre direttamente i valori
        $audioNodes = $xpath->query("//table[1]//tr[td[text()='Audio files']]/td[2]");
        $videoNodes = $xpath->query("//table[1]//tr[td[text()='Video files']]/td[2]");
        $imageNodes = $xpath->query("//table[1]//tr[td[text()='Image files']]/td[2]");
        
        // Estrai i valori
        $status->AUDIO = ($audioNodes->length > 0) ? (int)trim($audioNodes->item(0)->textContent) : 0;
        $status->VIDEO = ($videoNodes->length > 0) ? (int)trim($videoNodes->item(0)->textContent) : 0;
        $status->IMAGES = ($imageNodes->length > 0) ? (int)trim($imageNodes->item(0)->textContent) : 0;
                
        return $status;
    }







}