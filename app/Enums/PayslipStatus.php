<?php

namespace App\Enums;

enum PayslipStatus: string
{
    case DRAFT = 'draft';
    case FINAL = 'final';
}
