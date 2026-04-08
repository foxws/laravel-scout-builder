<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Tests\Fakes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
}
