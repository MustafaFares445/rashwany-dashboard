<?php

namespace App\Enums;

enum PackageRenewalType: string
{
    case Manual = 'manual';
    case Automatic = 'automatic';
}
