<?php

namespace App\Enum;

enum PostVisibility: string
{
    case PUBLIC = 'public';
    case FRIENDS = 'friends';
    case COLLEAGUES = 'colleagues';
    case PRIVATE = 'private';
}