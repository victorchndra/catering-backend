<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kitchen extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'year',
        'photo',
    ];

    public function cateringPackages(): HasMany {
        return $this->hasMany(CateringPackage::class);
    }
}
