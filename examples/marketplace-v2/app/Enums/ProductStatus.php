<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Suspended = 'suspended';
    case Archived = 'archived';
}
