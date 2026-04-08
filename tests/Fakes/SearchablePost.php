<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Tests\Fakes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class SearchablePost extends Model
{
    use Searchable;

    protected $guarded = [];

    public function scopePublished(Builder $builder): void
    {
        $builder->where('is_published', true);
    }

    public function scopeOfCategory(Builder $builder, mixed $category): void
    {
        $builder->where('category', $category);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(self::class, 'author_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(self::class, 'post_id');
    }
}
