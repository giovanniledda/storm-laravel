<?php
namespace App;

use Illuminate\Database\Eloquent\Model;


class Profession extends Model
{
    protected $table = 'professions';

    protected $fillable = [
       'name',
       'is_storm',
    ];
}