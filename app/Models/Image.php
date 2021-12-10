<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JoggApp\GoogleTranslate\GoogleTranslateFacade;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'text',
        'language',
    ];
}
