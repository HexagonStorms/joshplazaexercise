<?php
// disable error reporting in production
error_reporting(0);

// allow cors
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

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

// function to parse the html content
function parse_html_content($html) {
    // create dom document
    $dom = new DOMDocument();
    
    // load html content with error suppression
    @$dom->loadHTML($html);
    
    // create xpath
    $xpath = new DOMXPath($dom);
    
    // array to store the news
    $news = [];
    
    // get the main news clusters
    $items = $xpath->query('//div[contains(@class, "clus")]');
    
    // debug info
    error_log("Found " . $items->length . " news items on page");
    
    // loop through each item
    foreach ($items as $item) {
        // get headline - techmeme uses 'a' with class 'ourh'
        $headline = $xpath->query('.//a[contains(@class, "ourh")]', $item);
        
        // get source - techmeme uses 'cite' tags for author/publication
        $source = $xpath->query('.//cite', $item);
        
        // get summary - techmeme uses 'div' with class 'ii'
        $summary = $xpath->query('.//div[contains(@class, "ii")]', $item);
        
        // if headline exists
        if ($headline->length > 0) {
            // get headline text
            $headline_text = trim($headline->item(0)->nodeValue);
            
            // get source text from cite tag
            $author_text = 'Unknown';
            $publication_text = 'Unknown';
            
            if ($source->length > 0) {
                $source_text = trim($source->item(0)->textContent);
                // clean up the source text (remove trailing colon)
                $source_text = rtrim($source_text, ':');
                
                // separate author from publication
                // format is typically "Author Name / Publication Name"
                if (strpos($source_text, '/') !== false) {
                    $parts = explode('/', $source_text, 2);
                    $author_text = trim($parts[0]);
                    $publication_text = trim($parts[1]);
                } else {
                    // if no slash, assume it's just a publication
                    $publication_text = $source_text;
                }
            }
            
            // get summary text
            $summary_text = '';
            if ($summary->length > 0) {
                $summary_text = trim($summary->item(0)->nodeValue);
            }
            
            // add to news array
            $news[] = [
                'headline' => $headline_text,
                'author' => $author_text,
                'publication' => $publication_text,
                'full_source' => $author_text . ' / ' . $publication_text,
                'summary' => $summary_text
            ];
        }
    }
    
    return $news;
}

// function to calculate source counts
function calculate_source_counts($news) {
    // array to store source counts
    $source_counts = [];
    
    // loop through each news
    foreach ($news as $item) {
        // get source
        $source = $item['source'];
        
        // if source is empty, skip
        if (empty($source)) continue;
        
        // if source exists in source_counts, increment count
        if (isset($source_counts[$source])) {
            $source_counts[$source]++;
        } else {
            // else add to source_counts
            $source_counts[$source] = 1;
        }
    }
    
    // sort sources by count in descending order
    arsort($source_counts);
    
    // limit to top 10 sources
    return array_slice($source_counts, 0, 10, true);
}

// main function
function main() {
    // url to scrape
    $url = 'https://techmeme.com/';
    
    // get html content
    $html = get_html_content($url);
    
    // parse html content
    $news = parse_html_content($html);
    
    // calculate source counts
    $source_counts = calculate_source_counts($news);
    
    // return json response
    return [
        'news' => $news,
        'source_counts' => $source_counts
    ];
}

// run main function
$response = main();

// output json response
echo json_encode($response);