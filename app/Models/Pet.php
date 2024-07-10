<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Pet extends Model
{
    use HasFactory;

    protected $fillable = [ 
        "name",
        "type",
        "breed_id",
        "gender",
        "age",
        "weight",
        "img"
    ];

    public function report() {
        return $this->hasMany(Report::class, "petId");
    }

    public function adoptions() {
        return $this->hasMany(Adoption::class, "petId");
    }

    public function breed() {
        return $this->belongsTo(Breed::class, 'breed_id');
    }
}
