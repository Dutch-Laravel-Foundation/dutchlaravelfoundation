<?php

declare(strict_types=1);

namespace App\Services\Seo;

use GdImage;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Draws the 1200x630 social card with GD: a red title panel and a photo on the right.
 * The design follows public/assets/redesign/featured-image-editorial.html.
 */
final class OpenGraphImage
{
    /** Bump when the design changes, so social networks fetch the new image. */
    public const string VERSION = '1';

    private const int WIDTH = 1200;

    private const int HEIGHT = 630;

    private const int PANEL_WIDTH = 660;

    private const int PADDING = 58;

    private const int TITLE_MAX_WIDTH = 540;

    private const float TITLE_TRACKING = -0.045;

    /** Title size in px and the most lines allowed at that size. The last size may wrap to four lines. */
    private const array TITLE_SIZES = [[64, 2], [53, 3], [44, 4]];

    private const int STRIPE_ANGLE = 30;

    private const int STRIPE_GAP = 5;

    /**
     * Returns the path of the rendered JPEG, rendering it on the first request.
     */
    public function path(OpenGraphCard $card): string
    {
        $path = storage_path('app/og/'.$card->fingerprint().'.jpg');

        if (! File::exists($path)) {
            File::ensureDirectoryExists(dirname($path));

            $temporary = $path.'.'.bin2hex(random_bytes(4)).'.tmp';
            File::put($temporary, $this->render($card));
            File::move($temporary, $path);
        }

        return $path;
    }

    public function render(OpenGraphCard $card): string
    {
        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagealphablending($image, true);
        imageantialias($image, false);

        $red = $this->color($image, 0xFF, 0x2D, 0x20);
        imagefilledrectangle($image, 0, 0, self::PANEL_WIDTH - 1, self::HEIGHT - 1, $red);
        $this->stripes($image, 0, 0, self::PANEL_WIDTH - 1, self::HEIGHT - 1, $this->color($image, 0, 0, 0, 0.09));

        $this->drawLogo($image);
        $this->drawCopy($image, $card);
        $this->drawPhoto($image, $card);
        $this->drawCornerMark($image);
        $this->drawVerticalLabel($image);

        ob_start();
        imagejpeg($image, null, 88);

        return (string) ob_get_clean();
    }

    private function drawLogo(GdImage $image): void
    {
        $logo = imagecreatefrompng(resource_path('og/logo.png'));

        if ($logo === false) {
            throw new RuntimeException('Cannot read the Open Graph logo.');
        }

        imagecopy($image, $logo, self::PADDING, self::PADDING, 0, 0, imagesx($logo), imagesy($logo));
    }

    /**
     * Eyebrow, title and domain, aligned to the bottom of the red panel.
     */
    private function drawCopy(GdImage $image, OpenGraphCard $card): void
    {
        $white = $this->color($image, 255, 255, 255);
        [$size, $lines] = $this->fitTitle($card->title);
        $lineHeight = $size * 1.2;

        $y = self::HEIGHT - self::PADDING;
        $this->text($image, 'dutchlaravelfoundation.nl', self::PADDING, $y - 6, 18, $this->font('mulish-450'), 0, $this->color($image, 255, 255, 255, 0.85));
        $y -= 18 * 1.65 + 24;

        foreach (array_reverse($lines) as $line) {
            $baseline = $y - ($lineHeight - $size) / 2 - $size * 0.2;
            $this->text($image, $line, self::PADDING - 4, $baseline, $size, $this->font('mulish-520'), self::TITLE_TRACKING, $white);
            $y -= $lineHeight;
        }

        $this->text($image, mb_strtoupper($card->eyebrow), self::PADDING, $y - 18, 14, $this->font('jetbrains-mono-500'), 0.2, $white);
    }

    /**
     * @return array{0: int, 1: list<string>}
     */
    private function fitTitle(string $title): array
    {
        $font = $this->font('mulish-520');

        foreach (self::TITLE_SIZES as [$size, $maxLines]) {
            $lines = $this->wrap($title, $size, $font);

            if (count($lines) <= $maxLines) {
                return [$size, $lines];
            }
        }

        $lines = array_slice($lines, 0, $maxLines);
        $last = rtrim($lines[$maxLines - 1], ' .,:;-');

        while ($last !== '' && $this->width($last.'…', $size, $font, self::TITLE_TRACKING) > self::TITLE_MAX_WIDTH) {
            $last = rtrim(mb_substr($last, 0, -1));
        }

        $lines[$maxLines - 1] = $last.'…';

        return [$size, $lines];
    }

    /**
     * @return list<string>
     */
    private function wrap(string $text, int $size, string $font): array
    {
        $lines = [];
        $line = '';

        foreach (preg_split('/\s+/u', trim($text)) ?: [] as $word) {
            $candidate = $line === '' ? $word : "{$line} {$word}";

            if ($line !== '' && $this->width($candidate, $size, $font, self::TITLE_TRACKING) > self::TITLE_MAX_WIDTH) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }

        return [...$lines, $line];
    }

    /**
     * Covers the right side with the photo, cropped around its horizontal focus point.
     */
    private function drawPhoto(GdImage $image, OpenGraphCard $card): void
    {
        $contents = @file_get_contents($card->photoPath);
        $photo = $contents === false ? false : @imagecreatefromstring($contents);

        if ($photo === false) {
            throw new RuntimeException("Cannot read the Open Graph photo {$card->photoPath}.");
        }

        $targetWidth = self::WIDTH - self::PANEL_WIDTH;
        $sourceWidth = imagesx($photo);
        $sourceHeight = imagesy($photo);
        $scale = max($targetWidth / $sourceWidth, self::HEIGHT / $sourceHeight);
        $cropWidth = (int) round($targetWidth / $scale);
        $cropHeight = (int) round(self::HEIGHT / $scale);
        $cropX = (int) round(($sourceWidth - $cropWidth) * $card->photoFocusX);
        $cropY = (int) round(($sourceHeight - $cropHeight) / 2);

        imagecopyresampled($image, $photo, self::PANEL_WIDTH, 0, $cropX, $cropY, $targetWidth, self::HEIGHT, $cropWidth, $cropHeight);
    }

    private function drawCornerMark(GdImage $image): void
    {
        $color = $this->color($image, 255, 255, 255, 0.8);
        $right = self::WIDTH - 19;
        $bottom = self::HEIGHT - 19;

        imagesetthickness($image, 2);
        imageline($image, $right - 64, $bottom, $right, $bottom, $color);
        imageline($image, $right, $bottom - 64, $right, $bottom, $color);
        imagesetthickness($image, 1);
    }

    /**
     * The black "Dutch Laravel Foundation" tag in the top right corner.
     */
    private function drawVerticalLabel(GdImage $image): void
    {
        $label = 'DUTCH LARAVEL FOUNDATION';
        $font = $this->font('jetbrains-mono-500');
        $size = 11;
        $left = self::WIDTH - 54;
        $bottom = (int) round($this->width($label, $size, $font, 0.14) + 40);

        imagefilledrectangle($image, $left, 0, self::WIDTH - 1, $bottom, $this->color($image, 0x09, 0x09, 0x10));
        $this->stripes($image, $left, 0, self::WIDTH - 1, $bottom, $this->color($image, 255, 255, 255, 0.09));
        imagerectangle($image, $left, 0, self::WIDTH - 1, $bottom, $this->color($image, 255, 255, 255, 0.7));

        $this->text($image, $label, $left + 22, 20, $size, $font, 0.14, $this->color($image, 255, 255, 255), vertical: true);
    }

    /**
     * Thin diagonal lines, like the striped panels on the site.
     */
    private function stripes(GdImage $image, int $left, int $top, int $right, int $bottom, int $color): void
    {
        $slope = tan(deg2rad(self::STRIPE_ANGLE));
        $step = self::STRIPE_GAP / cos(deg2rad(self::STRIPE_ANGLE));
        $width = $right - $left;

        imagesetclip($image, $left, $top, $right, $bottom);

        for ($offset = -$width * $slope; $offset < $bottom - $top + $step; $offset += $step) {
            $y = (int) round($bottom - $offset);
            imageline($image, $left, $y, $right, (int) round($y - $width * $slope), $color);
        }

        imagesetclip($image, 0, 0, self::WIDTH - 1, self::HEIGHT - 1);
    }

    /**
     * Draws text with letter spacing in em. GD has no tracking, so each glyph is placed after
     * the measured prefix, which keeps the font's kerning.
     */
    private function text(
        GdImage $image,
        string $text,
        float $x,
        float $y,
        int $size,
        string $font,
        float $tracking,
        int $color,
        bool $vertical = false,
    ): void {
        $points = $this->points($size);
        $angle = $vertical ? -90 : 0;

        if ($tracking === 0.0) {
            imagettftext($image, $points, $angle, (int) round($x), (int) round($y), $color, $font, $text);

            return;
        }

        $glyphs = mb_str_split($text);

        foreach ($glyphs as $index => $glyph) {
            $offset = $this->advance(implode('', array_slice($glyphs, 0, $index)), $points, $font) + $index * $tracking * $size;

            imagettftext(
                $image,
                $points,
                $angle,
                (int) round($vertical ? $x : $x + $offset),
                (int) round($vertical ? $y + $offset : $y),
                $color,
                $font,
                $glyph,
            );
        }
    }

    private function width(string $text, int $size, string $font, float $tracking): float
    {
        return $this->advance($text, $this->points($size), $font)
            + max(0, mb_strlen($text) - 1) * $tracking * $size;
    }

    /**
     * Pen advance of a string, measured from the origin so overhanging glyphs such as "j" add no width.
     */
    private function advance(string $text, float $points, string $font): float
    {
        if ($text === '') {
            return 0;
        }

        $withMarker = imagettfbbox($points, 0, $font, $text.'H');
        $marker = imagettfbbox($points, 0, $font, 'H');

        if ($withMarker === false || $marker === false) {
            throw new RuntimeException("Cannot measure text with font {$font}.");
        }

        return $withMarker[2] - $marker[2];
    }

    /**
     * GD renders at 96 dpi, so CSS pixels convert to points at 3/4.
     */
    private function points(int $pixels): float
    {
        return $pixels * 0.75;
    }

    private function font(string $name): string
    {
        return resource_path("og/{$name}.ttf");
    }

    private function color(GdImage $image, int $red, int $green, int $blue, float $opacity = 1): int
    {
        $color = imagecolorallocatealpha($image, $red, $green, $blue, (int) round(127 * (1 - $opacity)));

        if ($color === false) {
            throw new RuntimeException('Cannot allocate a color for the Open Graph image.');
        }

        return $color;
    }
}
