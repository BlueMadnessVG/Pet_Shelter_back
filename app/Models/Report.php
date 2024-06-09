<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [ 
        "petId",
        "report"
    ];

    protected $hidden = [
        "petId"
    ];

    public function pet() {
        return $this->belongsTo(Pet::class, "petId");
    }
}
