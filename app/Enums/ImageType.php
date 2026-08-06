<?php

namespace App\Enums;

enum ImageType: string
{
    case Featured = 'featured';
    case Banner = 'banner';
    case Inline = 'inline';
    case Gallery = 'gallery';
}
