<?php

namespace App\Domain\Enums;

enum ReportChannel: string
{
    case All = 'all';
    case Pos = 'pos';
    case Online = 'web';
}
