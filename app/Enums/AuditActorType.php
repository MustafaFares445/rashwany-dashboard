<?php

namespace App\Enums;

enum AuditActorType: string
{
    case Admin = 'admin';
    case System = 'system';
}
