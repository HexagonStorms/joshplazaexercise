<?php
// disable error reporting in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// function to get the html content
function get_html_content($url) {
    // init curl
    $ch = curl_init();
    
    // set curl options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36');
    
    // execute curl
    $html = curl_exec($ch);
    
    // close curl
    curl_close($ch);
    
    return $html;
}

// url to scrape
$url = 'https://techmeme.com/';

// get html content
$html = get_html_content($url);

// display one sample item to examine structure
echo "<h1>Techmeme HTML Structure</h1>";

// find a div with class item
$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// find the first few items
$items = $xpath->query('//div[contains(@class, "clus")]');

echo "<p>Found " . $items->length . " items with class 'clus'</p>";

// display html structure of first 3 items
for ($i = 0; $i < min(3, $items->length); $i++) {
    $item = $items->item($i);
    echo "<h2>Item #" . ($i+1) . " HTML Structure:</h2>";
    echo "<pre>" . htmlspecialchars($dom->saveHTML($item)) . "</pre>";
    
    // also output as text to see spacing and content
    echo "<h3>Item #" . ($i+1) . " Text Structure:</h3>";
    echo "<pre>" . htmlspecialchars($item->textContent) . "</pre>";
    
    // try to find the source specifically
    $sources = $xpath->query('.//a[contains(@class, "pub")]', $item);
    echo "<h3>Source extraction attempts:</h3>";
    echo "<p>Found " . $sources->length . " elements with class 'pub'</p>";
    
    foreach ($sources as $source) {
        echo "<p>Source text: '" . htmlspecialchars($source->textContent) . "'</p>";
        echo "<p>Source HTML: '" . htmlspecialchars($dom->saveHTML($source)) . "'</p>";
    }
    
    echo "<hr>";
}
?>