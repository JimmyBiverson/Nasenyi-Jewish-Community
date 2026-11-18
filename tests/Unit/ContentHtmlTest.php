<?php

namespace Tests\Unit;

use App\Support\ContentHtml;
use PHPUnit\Framework\TestCase;

class ContentHtmlTest extends TestCase
{
    public function test_it_preserves_editorial_markup_and_drops_executable_markup(): void
    {
        $source = '<section id="history"><h2>History</h2><p onclick="alert(1)">Text &amp; sources</p>'
            .'<img src="/media/community/portrait.webp" srcset="/media/community/portrait.webp 800w, /media/community/portrait-small.webp 400w" alt="Portrait" onerror="alert(1)">'
            .'<a href="java&#x09;script:alert(1)">Link</a><a href="/about-us/">About</a>'
            .'<script>alert(2)</script><iframe src="https://example.com/embed"></iframe>'
            .'<iframe src="https://www.youtube.com/embed/sample" title="Video"></iframe></section>';

        $html = ContentHtml::sanitize($source);

        $this->assertStringContainsString('<h2>History</h2><p>Text &amp; sources</p>', $html);
        $this->assertStringContainsString('srcset="/media/community/portrait.webp 800w, /media/community/portrait-small.webp 400w"', $html);
        $this->assertStringContainsString('<a href="/about-us/">About</a>', $html);
        $this->assertStringContainsString('https://www.youtube.com/embed/sample', $html);
        $this->assertStringNotContainsString('javascript', $html);
        $this->assertStringNotContainsString('alert', $html);
        $this->assertStringNotContainsString('example.com', $html);
    }
}
