<?php

namespace App\Enums;

enum VendorStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
}
