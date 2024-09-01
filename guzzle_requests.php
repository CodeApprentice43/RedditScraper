<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

// Create a new Guzzle HTTP client
$httpClient = new Client();
$response = $httpClient->get('https://www.reddit.com/r/nav/top.json?limit=10'); // Fetch top posts in JSON format

// Get the JSON content from the response
$jsonString = $response->getBody()->getContents();

// Decode the JSON response
$data = json_decode($jsonString, true);

// Check for errors in JSON decoding
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error decoding JSON: " . json_last_error_msg() . PHP_EOL;
    exit;
}

// Prepare the HTML output
$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Politics Posts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            color: #333;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin-bottom: 20px;
        }
        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 10px 0;
        }
        video {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 10px 0;
        }
        a {
            color: #1a0dab;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <ul>';

// Loop through the posts and add them to the HTML list
foreach ($data['data']['children'] as $post) {
    $title = htmlspecialchars($post['data']['title']); // Escape HTML entities
    $html .= "<li><h2>{$title}</h2>";

    // Extract media information
    $mediaType = $post['data']['post_hint'] ?? '';
    $mediaUrl = $post['data']['url'] ?? '';

    // Handle image posts
    if ($mediaType === 'image') {
        $html .= "<img src=\"{$mediaUrl}\" alt=\"{$title}\">";
    }
    // Handle video posts
    elseif ($mediaType === 'video' || $mediaType === 'hosted:video') {
        $videoUrl = $post['data']['secure_media']['reddit_video']['fallback_url'] ?? '';
        if ($videoUrl) {
            $html .= "<video controls>
                        <source src=\"{$videoUrl}\" type=\"video/mp4\">
                        Your browser does not support the video tag.
                      </video>";
        }
    }
    // Handle link posts
    elseif (!empty($mediaUrl)) {
        $html .= "<a href=\"{$mediaUrl}\" target=\"_blank\">Read more</a>";
    }

    $html .= "</li>";
}

// Close the HTML tags
$html .= '</ul>
</body>
</html>';

// Output the HTML
echo $html;

