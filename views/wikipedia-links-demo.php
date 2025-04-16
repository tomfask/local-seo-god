<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Wikipedia Links Shortcode Demo</h1>
    
    <div class="card">
        <h2>How to Use the Placeslinks Shortcode</h2>
        <p>The placeslinks shortcode allows you to easily link to Wikipedia articles about geographic locations in your content.</p>
        
        <h3>Basic Usage:</h3>
        <pre>[placeslinks]Your content with [[Location]] links inside.[/placeslinks]</pre>
        
        <h3>Examples:</h3>
        <ul>
            <li><code>[[Melbourne]]</code> → Links to https://en.wikipedia.org/wiki/Melbourne</li>
            <li><code>[[Mornington, Victoria]]</code> → Links to https://en.wikipedia.org/wiki/Mornington,_Victoria</li>
            <li><code>[[Los Angeles, California]]</code> → Links to https://en.wikipedia.org/wiki/Los_Angeles,_California</li>
        </ul>
    </div>
    
    <div class="card" style="margin-top: 20px;">
        <h2>Live Demo</h2>
        
        <h3>Shortcode:</h3>
        <pre>[placeslinks]
Located in [[Melbourne]], our service area extends to suburbs like [[South Yarra]], [[Brighton]], and [[St Kilda]].
We also serve customers in [[Mornington, Victoria]] and [[Geelong]].
[/placeslinks]</pre>
        
        <h3>Result:</h3>
        <div class="placeslinks-demo-result">
            <?php
            // Get the shortcode output
            $shortcode_content = do_shortcode('[placeslinks]
Located in [[Melbourne]], our service area extends to suburbs like [[South Yarra]], [[Brighton]], and [[St Kilda]].
We also serve customers in [[Mornington, Victoria]] and [[Geelong]].
[/placeslinks]');
            
            echo $shortcode_content;
            ?>
        </div>
    </div>
    
    <div class="card" style="margin-top: 20px;">
        <h2>Usage in AI Content</h2>
        <p>When AI content mentions geographic locations, the system can automatically wrap them in double square brackets.
        Then, applying the placeslinks shortcode to that content will transform them into proper Wikipedia links.</p>
        
        <p>This is especially useful for:</p>
        <ul>
            <li>Location pages highlighting service areas</li>
            <li>Blog posts discussing local topics</li>
            <li>Content that references multiple geographic locations</li>
        </ul>
    </div>
</div>

<style>
    .card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    pre {
        background: #f5f5f5;
        padding: 10px;
        border: 1px solid #ddd;
        overflow: auto;
    }
    .placeslinks-demo-result {
        background: #f9f9f9;
        padding: 15px;
        border: 1px solid #eee;
        margin-top: 10px;
    }
</style> 