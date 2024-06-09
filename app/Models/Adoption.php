<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adoption extends Model
{
    use HasFactory;

    protected $fillable = [
        "userId",
        "petId"
    ];

    protected $hidden = [
        'userId',
        'petId',
    ];

    public function pet() {
        return $this->belongsTo(Pet::class, "petId");
    }

    public function user() {
        return $this->belongsTo(User::class,"userId");
     }
}
