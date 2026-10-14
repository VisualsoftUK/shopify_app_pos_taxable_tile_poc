<?php

namespace App\Domain\Enums;

enum ReportExportType: string
{
    case Orders = 'orders';
    case Daily = 'daily';
    case Variants = 'variants';
    case Customers = 'customers';
}
