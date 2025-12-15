<?php

namespace App\Enum;

enum NotificationType: string
{
    case LIKE_TO_POST = 'like_to_post';
    case DISLIKE_TO_POST = 'dislike_to_post';
    case COMMENT_ON_POST = 'comment_on_post';
    case SUBSCRIPTION = 'subscription';
    case MODERATOR_EDITED = 'moderator_edited';
    case MODERATOR_DELETED = 'moderator_deleted';
}