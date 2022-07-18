<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dummy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'priority',
        'is_published',
        // ...
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_published' => 'boolean',
        // ...
    ];

    /* =========================================================================
     * = Scopes
     * =========================================================================
     */

    /**
     * 按照優先度排序
     *
     * ```
     * $query->orderByPriority();
     * ```
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  boolean  $reversed
     * @return void
     */
    public function scopeOrderByPriority($query, $reversed = false)
    {
        $query->orderBy('priority', $reversed ? 'asc' : 'desc');
    }

    /**
     * 查詢僅上架
     *
     * ```php
     * $query->published();
     * ```
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  boolean  $value
     * @return void
     */
    public function scopePublished($query, $value = true)
    {
        $query->where('is_published', $value);
    }
}
