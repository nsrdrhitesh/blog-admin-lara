<?php

namespace App\Enums;

enum SchemaType: string
{
    case Article = 'article';
    case Breadcrumb = 'breadcrumb';
    case Faq = 'faq';
    case Organization = 'organization';
    case Person = 'person';
    case SearchAction = 'search_action';
    case Image = 'image';
    case Video = 'video';
    case HowTo = 'howto';
    case Speakable = 'speakable';
}
