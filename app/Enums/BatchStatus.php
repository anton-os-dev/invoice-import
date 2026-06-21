<?php

namespace App\Enums;

enum BatchStatus: string
{
    case Pending = 'pending';
    case Validated = 'validated';
    case Processing = 'processing';
    case Completed = 'completed';
    case PartiallyCompleted = 'partially_completed';
    case Failed = 'failed';
}
