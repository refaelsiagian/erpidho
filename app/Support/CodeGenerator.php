<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class CodeGenerator
{
    /**
     * Generate the next sequential formatted code for a model column.
     *
     * @param  class-string<Model>  $modelClass
     */
    public static function next(string $modelClass, string $column, string $prefix, int $padLength = 6): string
    {
        $latestCode = $modelClass::query()
            ->orderByDesc('id')
            ->value($column);

        $nextNumber = 1;

        if (is_string($latestCode) && preg_match('/(\d+)$/', $latestCode, $matches) === 1) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%0'.$padLength.'d', $prefix, $nextNumber);
    }
}
