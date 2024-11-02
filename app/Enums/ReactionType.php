<?php

namespace App\Enums;

enum ReactionType: string
{
    case LIKE = 'like';
    case DISLIKE = 'dislike';
}
