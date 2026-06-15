<?php

namespace App\Enums;

enum UserRole: string
{
    case Operator = 'operator';
    case Creator = 'creator';
}
