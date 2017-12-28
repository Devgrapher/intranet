<?php

namespace Intra\Lib\Azure;

class GraphServiceAccessHelper
{
    // Constructs a Http GET request to fetch the entry for the current user.
    // Returns the json decoded respone as the objects that were recieved in feed.
    public static function getMeEntry(array $token_array)
    {
        // initiaze curl which is used to make the http request
        $ch = curl_init();

        // Add authorization and other headers. Also set some common settings.
        self::addRequiredHeadersAndSettings($ch, $token_array);

        // Create url for the entry based on the feedname and the key value
        $feedURL = "https://graph.windows.net/" . Settings::getAppTenantDomainName() . "/me/";
        //  $feedURL = "https://graph.windows.net/me";
        $feedURL = $feedURL . "?" . Settings::getApiVersion();
        curl_setopt($ch, CURLOPT_URL, $feedURL);

        //Enable fiddler to capture request
        //curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);
        $jsonOutput = json_decode($output);

        return $jsonOutput;
    }

    // Constructs a HTTP PATCH request for updating current user entry.

    public static function addRequiredHeadersAndSettings($ch, array $token_array)
    {
        //Generate the authentication header
        $authHeader = 'Authorization:' . $token_array['tokenType'] . ' ' . $token_array['accessToken'];
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                $authHeader,
                'Accept:application/json;odata=minimalmetadata',
                'Content-Type:application/json'
            ]
        );
        // Set the option to recieve the response back as string.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // By default https does not work for CURL.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    // Add required headers like Authorization, Accept, Content-Type etc.

    public static function updateMeEntry($entry, array $token_array)
    {
        //initiaze curl which is used to make the http request
        $ch = curl_init();
        self::addRequiredHeadersAndSettings($ch, $token_array);
        // set url
        $feedURL = "https://graph.windows.net/me" . "?" . Settings::getApiVersion();
        curl_setopt($ch, CURLOPT_URL, $feedURL);
        // Mark as Patch request
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        $data = json_encode($entry);
        // Set the data for the request
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // read the output from the request
        $output = curl_exec($ch);
        // close curl resource to free up system resources
        curl_close($ch);
        // decode the response json decoder
        $udpatedEntry = json_decode($output);

        return $udpatedEntry;
    }
}
