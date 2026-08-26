<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentModel extends Model
{
    protected $table = 'parents_data';

    protected $fillable = ['name', 'phone', 'address', 'email', 'occupation'];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
}
