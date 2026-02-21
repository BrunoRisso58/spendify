<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertType extends Model
{
    protected $fillable = ['name', 'label', 'description'];

    public function alerts()
    {
        // return $this->hasMany(Alert::class, 'type_id');
    }
}
