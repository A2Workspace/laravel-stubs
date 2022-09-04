<?php

namespace App\Models;

use Database\Factories\DummyFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @method static \Illuminate\Database\Eloquent\Builder orderByPriority(bool $reversed = false)
 * @method static \Illuminate\Database\Eloquent\Builder published()
 */
class Dummy extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dummies';

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

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return DummyFactory::new();
    }

    /* =========================================================================
     * = Relations
     * =========================================================================
     */

    // /**
    //  * @return \Illuminate\Database\Eloquent\Relations\Relation
    //  */
    // public function relation(): Relations\Relation
    // {
    //     return $this->belongsTo(Relation::class);
    // }

    /* =========================================================================
     * = Scopes
     * =========================================================================
     */

    /**
     * ```php
     * Dummy::orderByPriority();
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
     * ```php
     * Dummy::published();
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
