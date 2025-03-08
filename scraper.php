<?php
// cors
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// standard curl to get the html content
function getHtmlContent($url) {

    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36');
    
    $html = curl_exec($ch);
    
    curl_close($ch);
    
    return $html;
}

// function to parse the html content
function parseHtmlContent($html) {

    // load html
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $news = [];
    
    $items = $xpath->query('//div[contains(@class, "clus")]');
    
    foreach ($items as $item) {
        // use xpath to extract data
        $headline = $xpath->query('.//a[contains(@class, "ourh")]', $item);
        $source = $xpath->query('.//cite', $item);
        $summary = $xpath->query('.//div[contains(@class, "ii")]', $item);
        
        if ($headline->length > 0) {
            $headline_text = trim($headline->item(0)->nodeValue);
            
            // placeholders
            $author_text = 'Unknown';
            $publication_text = 'Unknown';
            
            if ($source->length > 0) {
                $source_text = trim($source->item(0)->textContent);
                $source_text = rtrim($source_text, ':');
                
                // split source text into author and publication for techmeme publication
                if (strpos($source_text, '/') !== false) {
                    $parts = explode('/', $source_text, 2);
                    $author_text = trim($parts[0]);
                    $publication_text = trim($parts[1]);
                } else {
                    // no slash, assume it's a publication
                    $publication_text = $source_text;
                }
            }
            
            // get summary text
            $summary_text = '';
            if ($summary->length > 0) {
                $summary_text = trim($summary->item(0)->nodeValue);
            }
            
            // build news array
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
function calculateSourceCounts($news) {
    $sourceCounts = [];
    
    foreach ($news as $item) {
        $source = $item['publication'];
        
        // if source is empty, skip
        if (empty($source)) continue;
        
        // if source exists in sourceCounts, increment count
        if (isset($sourceCounts[$source])) {
            $sourceCounts[$source]++;
        } else {
            // else add to sourceCounts
            $sourceCounts[$source] = 1;
        }
    }
    
    // sort sources by count in descending order
    arsort($sourceCounts);
    
    // limiting to top 10 sources
    return array_slice($sourceCounts, 0, 10, true);
}


function extractKeywords($news) {
    $keywordCounts = [];
    
    // common words to exclude
    $stopWords = [
        'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'with', 'by', 
        'about', 'as', 'of', 'its', 'it', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 
        'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'shall', 'should', 
        'can', 'could', 'may', 'might', 'that', 'this', 'these', 'those', 'from', 'after',
        'before', 'while', 'during', 'under', 'over', 'above', 'below', 'up', 'down', 
        'into', 'out', 'through', 'between', 'among', 'per', 'via', 'than', 'etc'
    ];
    
    foreach ($news as $item) {
        // combine headline and summary for keyword extraction
        $text = $item['headline'] . ' ' . $item['summary'];

        // convert to lowercase and remove punctuation
        $text = strtolower($text);
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($words as $word) {
            if (strlen($word) < 3 || in_array($word, $stopWords)) {
                continue;
            }
            
            if (isset($keywordCounts[$word])) {
                $keywordCounts[$word]++;
            } else {
                $keywordCounts[$word] = 1;
            }
        }
    }
    
    // sort keywords by count in descending order, limit to top 10
    arsort($keywordCounts);
    return array_slice($keywordCounts, 0, 10, true);
}

function executeScrape() {
    $url = 'https://techmeme.com/';
    $html = getHtmlContent($url);
    $news = parseHtmlContent($html);
    $sourceCounts = calculateSourceCounts($news);
    $keywords = extractKeywords($news);
    
    return [
        'news' => $news,
        'sourceCounts' => $sourceCounts,
        'keywords' => $keywords
    ];
}

$response = executeScrape();
echo json_encode($response);