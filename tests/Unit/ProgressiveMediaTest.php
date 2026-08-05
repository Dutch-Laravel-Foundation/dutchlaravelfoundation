<?php

declare(strict_types=1);
use App\Modifiers\ProgressiveMedia;

it('enhances local raster images with stable loading markup', function () {
    $html = '<p><img src="/assets/uploads/assets/vragen-ai-dashboard.jpg" alt="Dashboard"></p>';

    $result = (new ProgressiveMedia)->index($html);

    $this->assertStringContainsString('data-progressive-media-frame', $result);
    $this->assertStringContainsString('width="3290"', $result);
    $this->assertStringContainsString('height="1516"', $result);
    $this->assertStringContainsString('loading="lazy"', $result);
    $this->assertStringContainsString('decoding="async"', $result);
    $this->assertStringContainsString('data-progressive-media=""', $result);
    $this->assertStringContainsString('data-media-state="loading"', $result);
    $this->assertStringNotContainsString('onload=', $result);
});
it('maps production asset urls to local dimensions', function () {
    $html = '<p><img src="https://dutchlaravelfoundation.nl/assets/uploads/assets/pint-output.jpg" alt="Pint output"></p>';

    $result = (new ProgressiveMedia)->index($html);

    expect($result)->toMatch('/width="[1-9][0-9]*"/');
    expect($result)->toMatch('/height="[1-9][0-9]*"/');
    $this->assertStringContainsString('loading="lazy"', $result);
});
it('uses and removes an external media dimension hint', function () {
    $html = '<p><img src="https://example.com/demo.gif#media-800x450" alt="Demo"></p>';

    $result = (new ProgressiveMedia)->index($html);

    $this->assertStringContainsString('src="https://example.com/demo.gif"', $result);
    $this->assertStringContainsString('width="800"', $result);
    $this->assertStringContainsString('height="450"', $result);
    $this->assertStringContainsString('data-progressive-media=""', $result);
    $this->assertStringNotContainsString('#media-800x450', $result);
});
it('adds an empty alt attribute when editorial content omits one', function () {
    $html = '<p><img src="/assets/uploads/assets/vragen-ai-dashboard.jpg"></p>';

    $result = (new ProgressiveMedia)->index($html);

    $this->assertStringContainsString('alt=""', $result);
});
it('leaves svg and already enhanced images alone', function () {
    $html = '<p><img src="/assets/img/proven-secure.svg" alt=""><img src="/photo.jpg" alt="" data-progressive-media loading="eager"></p>';

    $result = (new ProgressiveMedia)->index($html);

    expect($result)->toBe($html);
});
