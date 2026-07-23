<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modifiers\ProgressiveMedia;
use Tests\TestCase;

class ProgressiveMediaTest extends TestCase
{
    public function testItEnhancesLocalRasterImagesWithStableLoadingMarkup(): void
    {
        $html = '<p><img src="/assets/uploads/assets/vragen-ai-dashboard.jpg" alt="Dashboard"></p>';

        $result = (new ProgressiveMedia())->index($html);

        $this->assertStringContainsString('data-progressive-media-frame', $result);
        $this->assertStringContainsString('width="3290"', $result);
        $this->assertStringContainsString('height="1516"', $result);
        $this->assertStringContainsString('loading="lazy"', $result);
        $this->assertStringContainsString('decoding="async"', $result);
        $this->assertStringContainsString('data-progressive-media=""', $result);
        $this->assertStringContainsString('data-media-state="loading"', $result);
        $this->assertStringContainsString('performance.getEntriesByName(this.currentSrc)', $result);
        $this->assertStringContainsString('new window.URL(this.currentSrc,location.href)', $result);
        $this->assertStringContainsString("this.dataset.mediaCached=''", $result);
        $this->assertStringContainsString("this.dataset.mediaState='loaded'", $result);
    }

    public function testItMapsProductionAssetUrlsToLocalDimensions(): void
    {
        $html = '<p><img src="https://dutchlaravelfoundation.nl/assets/uploads/assets/pint-output.jpg" alt="Pint output"></p>';

        $result = (new ProgressiveMedia())->index($html);

        $this->assertMatchesRegularExpression('/width="[1-9][0-9]*"/', $result);
        $this->assertMatchesRegularExpression('/height="[1-9][0-9]*"/', $result);
        $this->assertStringContainsString('loading="lazy"', $result);
    }

    public function testItUsesAndRemovesAnExternalMediaDimensionHint(): void
    {
        $html = '<p><img src="https://example.com/demo.gif#media-800x450" alt="Demo"></p>';

        $result = (new ProgressiveMedia())->index($html);

        $this->assertStringContainsString('src="https://example.com/demo.gif"', $result);
        $this->assertStringContainsString('width="800"', $result);
        $this->assertStringContainsString('height="450"', $result);
        $this->assertStringContainsString('data-progressive-media=""', $result);
        $this->assertStringNotContainsString('#media-800x450', $result);
    }

    public function testItAddsAnEmptyAltAttributeWhenEditorialContentOmitsOne(): void
    {
        $html = '<p><img src="/assets/uploads/assets/vragen-ai-dashboard.jpg"></p>';

        $result = (new ProgressiveMedia())->index($html);

        $this->assertStringContainsString('alt=""', $result);
    }

    public function testItLeavesSvgAndAlreadyEnhancedImagesAlone(): void
    {
        $html = '<p><img src="/assets/img/proven-secure.svg" alt=""><img src="/photo.jpg" alt="" data-progressive-media loading="eager"></p>';

        $result = (new ProgressiveMedia())->index($html);

        $this->assertSame($html, $result);
    }
}
