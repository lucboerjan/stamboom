<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Plaats extends Model
{
    use HasFactory;
    protected $table = 'plaats';

    /**
     * public function plaatsenLijst()
     * haalt lijst op met plaatsen
     * @return array
     */
    public function plaatsenLijst()
    {
        $dbSql = sprintf('
            SELECT id, gemeente, land
            FROM plaats
            ORDER BY land, gemeente
        ');

        return DB::select($dbSql);
    }
}
