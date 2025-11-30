<?php
// includes/performance.php

// Enable GZIP compression if available
if (extension_loaded('zlib') && !ob_start("ob_gzhandler")) {
    ob_start();
}

// Set caching headers for better performance
header("Cache-Control: public, max-age=86400"); // 1 day cache
header("Expires: " . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

// Set content type and charset
header("Content-Type: text/html; charset=utf-8");

// Browser caching for static resources
function setStaticCachingHeaders($file_extension) {
    $cache_time = 31536000; // 1 year for static assets
    
    switch($file_extension) {
        case 'css':
            header("Content-Type: text/css");
            break;
        case 'js':
            header("Content-Type: application/javascript");
            break;
        case 'jpg':
        case 'jpeg':
            header("Content-Type: image/jpeg");
            break;
        case 'png':
            header("Content-Type: image/png");
            break;
        case 'gif':
            header("Content-Type: image/gif");
            break;
        case 'svg':
            header("Content-Type: image/svg+xml");
            break;
    }
    
    header("Cache-Control: public, max-age=" . $cache_time);
    header("Expires: " . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
    header("Pragma: cache");
}

// Critical CSS inlining function
function getCriticalCSS() {
    return '
    <style>
    /* Critical above-the-fold styles */
    .loader-container { 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: var(--gradient-primary); 
        z-index: 9999; 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        justify-content: center; 
        color: white; 
        transition: opacity 0.5s ease;
    }
    .loader-text { 
        font-size: 2rem; 
        font-weight: 800; 
        margin-bottom: 20px; 
        text-align: center; 
    }
    .loader-sequence { 
        display: flex; 
        gap: 10px; 
    }
    .loader-word { 
        opacity: 0.3; 
        transition: opacity 0.5s ease; 
        font-size: 1.5rem;
        font-weight: 700;
    }
    .loader-word.active { 
        opacity: 1; 
    }
    .progress-loader { 
        width: 200px; 
        height: 4px; 
        background: rgba(255,255,255,0.3); 
        border-radius: 2px; 
        overflow: hidden; 
        margin-top: 20px; 
    }
    .progress-bar { 
        height: 100%; 
        background: var(--accent); 
        width: 0%; 
        transition: width 0.3s ease; 
    }
    </style>';
}

// Lazy loading helper function
function lazyImage($src, $alt, $class = '', $width = '', $height = '') {
    // Use a placeholder for missing images
    if (empty($src)) {
        $src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIG5vdCBmb3VuZDwvdGV4dD48L3N2Zz4=';
    }
    
    $loading_attr = 'loading="lazy"';
    $dimensions = '';
    
    if ($width) $dimensions .= ' width="' . $width . '"';
    if ($height) $dimensions .= ' height="' . $height . '"';
    
    return '<img src="' . $src . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '" ' . $loading_attr . $dimensions . '>';
}

// Function to check if image exists
function imageExists($path) {
    if (empty($path)) return false;
    
    // Check if it's a URL
    if (filter_var($path, FILTER_VALIDATE_URL)) {
        $headers = @get_headers($path);
        return $headers && strpos($headers[0], '200');
    }
    
    // Check local file
    return file_exists($path);
}
?>