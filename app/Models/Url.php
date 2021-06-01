<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Url extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'url', 'valid'];
    protected $dates    = ['valid'];

    public function clicks()
    {
        return $this->hasMany(Click::class);
    }
}
