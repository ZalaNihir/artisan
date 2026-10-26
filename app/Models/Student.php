<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['first_name', 'last_name', 'phone_number', 'email', 'image', 'dob', 'gender', 'address', 'teacher_id', 'status'])]
class Student extends Model
{
    protected function casts(): array
    {
        return ['dob' => 'date'];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
