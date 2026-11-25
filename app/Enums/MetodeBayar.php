<?php

namespace App\Enums;

enum MetodeBayar: string
{
    case Tunai = 'Tunai';
    case Transfer = 'Transfer';
    case Qris = 'QRIS';
}

