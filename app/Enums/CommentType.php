<?php

namespace App\Enums;

enum CommentType: string
{
    case ADD = 'add';
    case REPLY = 'reply';
}
