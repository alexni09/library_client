<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model {
    protected $fillable = ['user_id', 'exemplar_id'];

    /* Relationships */
    public function user() { return $this->belongsTo(User::class); }
}