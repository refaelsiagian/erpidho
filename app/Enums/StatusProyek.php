<?php

namespace App\Enums;

enum StatusProyek: string
{
    case Menunggu = 'Menunggu';
    case Berjalan = 'Berjalan';
    case Selesai = 'Selesai';
}
