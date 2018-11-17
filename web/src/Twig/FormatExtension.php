<?php

namespace SourceBans\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FormatExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('formatLength', [$this, 'formatLength']),
            new TwigFilter('formatLengthText', [$this, 'formatLengthText']),
            new TwigFilter('formatSize', [$this, 'formatSize']),
        ];
    }

    /**
     * Format seconds as "12:34:56".
     *
     * @param int $secs
     *
     * @return string
     */
    public function formatLength(int $secs): string
    {
        $hours = (int) ($secs / 60 / 60);
        $secs -= $hours * 60 * 60;
        $mins = (int) ($secs / 60);
        $secs %= 60;

        return $hours.':'.$mins.':'.$secs;
    }

    /**
     * Format seconds as "1 yr, 2 mo, 3 wk, 4 d, 5 hr, 6 min, 7 sec".
     *
     * @param int $secs
     *
     * @return string
     */
    public function formatLengthText(int $secs): string
    {
        $ret = '';
        $units = [
            'yr' => 60 * 60 * 24 * 365,
            'mo' => 60 * 60 * 24 * 30,
            'wk' => 60 * 60 * 24 * 7,
            'd' => 60 * 60 * 24,
            'hr' => 60 * 60,
            'min' => 60,
            'sec' => 1,
        ];

        foreach ($units as $name => $div) {
            if (!($value = (int) ($secs / $div))) {
                continue;
            }

            $ret .= ', '.$value.' '.$name;
            $secs %= $div;
        }

        return substr($ret, 2);
    }

    /**
     * Format size in English units.
     *
     * @param int $size      The size in bytes
     * @param int $precision The optional number of decimal digits to round to
     *
     * @return string
     */
    public function formatSize(int $size, int $precision = 2): string
    {
        $sizes = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        for ($i = 0; $size > 1024 && $i < count($sizes) - 1; ++$i) {
            $size /= 1024;
        }

        return round($size, $precision).' '.$sizes[$i];
    }
}
