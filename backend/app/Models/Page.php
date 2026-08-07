<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'body_html',
        'cover_image_path',
        'status',
        'published_at',
        'created_by',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
