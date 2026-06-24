<?php

namespace App\Helpers;

class IconHelper
{
    /**
     * Map of image paths to Heroicon names
     *
     * @var array
     */
    protected static $iconMap = [
        'img/svg/speedometer.svg' => 'chart-bar',
        'img/svg/buildings.svg' => 'building-office',
        'img/svg/file-medical.svg' => 'document-plus',
        'img/svg/building-fill.svg' => 'building',
        'img/svg/people.svg' => 'users',
        'img/svg/ticket-detailed.svg' => 'ticket',
        'img/svg/credit-card-2-front.svg' => 'credit-card',
        'img/svg/file-earmark-medical.svg' => 'document-text',
        'img/svg/person-circle.svg' => 'user-circle',
        'img/svg/file-text.svg' => 'document',
        'img/svg/file-earmark-arrow-down.svg' => 'document-arrow-down',
        'img/svg/question-circle.svg' => 'question-mark-circle',
        'img/svg/info.svg' => 'information-circle',
        'img/svg/files.svg' => 'document-duplicate',
        'img/svg/person-lines.svg' => 'user',
        'img/svg/calendar-event.svg' => 'calendar',
        'img/svg/boxes.svg' => 'cube',
        'img/svg/circle-fill.svg' => 'circle',
        'img/svg/icon-mask-diver-white.svg' => 'globe-alt',
        'img/svg/compass.svg' => 'compass',
        'img/svg/book.svg' => 'book-open',
        'img/svg/money.svg' => 'currency-dollar',
        'img/svg/people-fill.svg' => 'user-group',
        'img/svg/house-door.svg' => 'home',
        'img/svg/icon-athlete-white.svg' => 'user',
        'img/svg/person-rolodex.svg' => 'identification',
        'img/svg/icon-student-cap-white.svg' => 'academic-cap',
        'img/svg/person-workspace.svg' => 'briefcase',
        'img/svg/app-window.svg' => 'window',
    ];

    /**
     * Get the Heroicon name for a given image path
     *
     * @param  string  $imagePath
     * @return string
     */
    public static function getHeroiconName($imagePath)
    {
        return self::$iconMap[$imagePath] ?? 'menu';
    }
}
