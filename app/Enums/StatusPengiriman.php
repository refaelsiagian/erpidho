<?php

namespace App\Enums;

enum StatusPengiriman: string
{
    case Terjadwal = 'Terjadwal';
    case Dikirim = 'Dikirim';
    case Selesai = 'Selesai';
}
