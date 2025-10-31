<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColorScheme extends Model
{
    protected $fillable = [
        'name', 'primary_color', 'secondary_color', 'accent_color', 'neutral_dark', 'neutral_light'
    ];
}
