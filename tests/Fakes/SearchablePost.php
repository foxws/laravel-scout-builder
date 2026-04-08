<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Tests\Fakes;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class SearchablePost extends Model
{
    use Searchable;

    protected $guarded = [];
}
