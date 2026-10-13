<?php

namespace App\Domain\Enums;

enum VatBehaviour: string
{
    case Standard = 'standard';
    case Zero = 'zero';
    case Dynamic = 'dynamic';
}
