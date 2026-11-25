<?php

namespace App\Enums;

enum StatusPembayaran: string
{
    case Lunas = 'Lunas';
    case BelumLunas = 'Belum Lunas';
}

