<?php

// Get the contents of a URL
function curl_get_contents($url): string
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// Validate parameters and return param if valid and default otherwise
function validateParam($name, $pattern, $defaults): string
{
    if (isset($_GET[$name]) && preg_match($pattern, $_GET[$name])) {
        return $_GET[$name];
    } else {
        return $defaults[$name];
    }
}

// Use YouTube Data API and Shields.io to get an image URL
function getShieldURL($query, $defaults): string 
{
    // Check for and validate parameters
    $color = validateParam("color", "/^([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[A-Za-z]+)$/", $defaults);
    $logo = validateParam("logo", "/^[A-Za-z0-9\-]+$/", $defaults);
    $logoColor = validateParam("logoColor", "/^([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[A-Za-z]+)$/", $defaults);
    $style = validateParam("style", "/^[A-Za-z\-]+$/", $defaults);
    $label = validateParam("label", "/^[^#&?<>]+$/", $defaults);
    $labelColor = validateParam("labelColor", "/^([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[A-Za-z]+)$/", $defaults);
    $id = validateParam("id", "/^[A-Za-z0-9\-\_]+$/", $defaults);
    $key = validateParam("key", "/^[A-Za-z0-9_]+$/", $defaults);

    // API key was not specified in URL or defaults
    if (preg_match("/^\[.*\]$/", $key)) {
        die("Your API key is missing or invalid.");
    }

    // URL for accessing the YouTube Data API
    $apiUrl = "https://www.googleapis.com/youtube/v3/channels?";
    // Add Youtube API parameters
    $apiUrl .= http_build_query(array(
        "part" => "statistics",
        "id" => $id,
        "alt" => "json",
        "key" => $key
    ));

    // Shields.io URL parameters
    $params = array(
        "color" => $color,
        "logo" => $logo,
        "logoColor" => $logoColor,
        "style" => $style,
        "label" => $label,
        "labelColor" => $labelColor,
        "query" => $query,
        "url" => $apiUrl
    );

    // Build the Shields.io url using the above parameters and JSON query
    return "https://img.shields.io/badge/dynamic/json?" . http_build_query($params);
}

// formats response number according to chosen format
function formatResponseNumber($response, $format, $style): string {
    switch ($format) {
        case "commas":
            // Adding Commas
            preg_match_all('!\d+!', strip_tags($response), $matches);
            return str_replace($matches[0][0], number_format($matches[0][0]), $response);
        case "short":
            // shortening number
            preg_match_all('!\d+!', strip_tags($response), $matches);
            $number = shortNumber($matches[0][0]);
            if ('for-the-badge' === $style) {
                $number = strtoupper($number);
            }
            return str_replace($matches[0][0], $number, $response);
        case "none"; // fallthrough
        default:
            return $response;
    }
}

// rounds a number to first decimal point and adds appropriate label for amount
function shortNumber($num): string
{
    $units = ['', 'k', 'm', 'b', 't'];
    for ($i = 0; $num >= 1000; $i++) {
        $num /= 1000;
    }
    return round($num, 1) . $units[$i];
}
