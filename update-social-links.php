<?php
// Script to update social media links in all HTML files
// Run this script to update all remaining files with the correct social media links

$social_links_old = [
    'href="#" aria-label="Follow us on Facebook"><i class="fa-brands fa-facebook-f"></i></a>',
    'href="#" aria-label="Follow us on Twitter"><i class="fa-brands fa-twitter"></i></a>',
    'href="#" aria-label="Follow us on WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>',
    'href="#" aria-label="Follow us on Skype"><i class="fa-brands fa-skype"></i></a>',
    'href="#"><i class="fa-brands fa-facebook-f"></i></a>',
    'href="#"><i class="fa-brands fa-twitter"></i></a>',
    'href="#"><i class="fa-brands fa-whatsapp"></i></a>',
    'href="#"><i class="fa-brands fa-skype"></i></a>'
];

$social_links_new = [
    'href="https://www.facebook.com/profile.php?id=61580125714254" aria-label="Follow us on Facebook"><i class="fa-brands fa-facebook-f"></i></a>',
    'href="https://www.instagram.com/krafthaus.in/" aria-label="Follow us on Instagram"><i class="fa-brands fa-instagram"></i></a>',
    'href="https://www.linkedin.com/company/kraft-haus/?viewAsMember=true" aria-label="Connect with us on LinkedIn"><i class="fa-brands fa-linkedin"></i></a>'
];

// Get all HTML files
$html_files = glob('*.html');

$updated_files = [];
$skipped_files = [];

foreach ($html_files as $file) {
    echo "Processing: $file\n";
    
    $content = file_get_contents($file);
    $original_content = $content;
    
    // Update individual social media links
    $content = str_replace('href="#"><i class="fa-brands fa-facebook-f"></i></a>', 'href="https://www.facebook.com/profile.php?id=61580125714254" aria-label="Follow us on Facebook"><i class="fa-brands fa-facebook-f"></i></a>', $content);
    $content = str_replace('href="#"><i class="fa-brands fa-twitter"></i></a>', 'href="https://www.instagram.com/krafthaus.in/" aria-label="Follow us on Instagram"><i class="fa-brands fa-instagram"></i></a>', $content);
    $content = str_replace('href="#"><i class="fa-brands fa-whatsapp"></i></a>', 'href="https://www.linkedin.com/company/kraft-haus/?viewAsMember=true" aria-label="Connect with us on LinkedIn"><i class="fa-brands fa-linkedin"></i></a>', $content);
    $content = str_replace('href="#"><i class="fa-brands fa-skype"></i></a>', '', $content);
    
    // Update with aria-label versions
    $content = str_replace('href="#" aria-label="Follow us on Facebook"><i class="fa-brands fa-facebook-f"></i></a>', 'href="https://www.facebook.com/profile.php?id=61580125714254" aria-label="Follow us on Facebook"><i class="fa-brands fa-facebook-f"></i></a>', $content);
    $content = str_replace('href="#" aria-label="Follow us on Twitter"><i class="fa-brands fa-twitter"></i></a>', 'href="https://www.instagram.com/krafthaus.in/" aria-label="Follow us on Instagram"><i class="fa-brands fa-instagram"></i></a>', $content);
    $content = str_replace('href="#" aria-label="Follow us on WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>', 'href="https://www.linkedin.com/company/kraft-haus/?viewAsMember=true" aria-label="Connect with us on LinkedIn"><i class="fa-brands fa-linkedin"></i></a>', $content);
    $content = str_replace('href="#" aria-label="Follow us on Skype"><i class="fa-brands fa-skype"></i></a>', '', $content);
    
    // Clean up empty list items
    $content = preg_replace('/<li><a href=""><\/a><\/li>/', '', $content);
    $content = preg_replace('/<li><\/li>/', '', $content);
    
    if ($content !== $original_content) {
        file_put_contents($file, $content);
        $updated_files[] = $file;
        echo "✅ Updated: $file\n";
    } else {
        $skipped_files[] = $file;
        echo "⏭️  Skipped: $file (no changes needed)\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Updated files: " . count($updated_files) . "\n";
echo "Skipped files: " . count($skipped_files) . "\n";

if (!empty($updated_files)) {
    echo "\nUpdated files:\n";
    foreach ($updated_files as $file) {
        echo "- $file\n";
    }
}

if (!empty($skipped_files)) {
    echo "\nSkipped files:\n";
    foreach ($skipped_files as $file) {
        echo "- $file\n";
    }
}

echo "\n✅ Social media links update completed!\n";
?>
