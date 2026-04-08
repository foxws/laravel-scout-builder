<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Foxws\ScoutBuilder\Concerns\FiltersQuery;
use Foxws\ScoutBuilder\Concerns\SortsQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\ForwardsCalls;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @template TModel of Model
 *
 * @mixin ScoutBuilder<TModel>
 */
class QueryBuilder
{
    use FiltersQuery;
    use ForwardsCalls;
    use SortsQuery;

    protected QueryBuilderRequest $request;

    public function __construct(
        protected ScoutBuilder $subject,
        ?Request $request = null,
    ) {
        $this->request = $request
            ? QueryBuilderRequest::fromRequest($request)
            : app(QueryBuilderRequest::class);
    }

    public function getScoutBuilder(): ScoutBuilder
    {
        return $this->subject;
    }

    public function getSubject(): ScoutBuilder
    {
        return $this->subject;
    }

    /**
     * @template T of Model
     *
     * @param  ScoutBuilder<T>|T|class-string<T>  $subject
     * @return static<T>
     */
    public static function for(ScoutBuilder|Model|string $subject, ?Request $request = null): static
    {
        if (is_string($subject) && is_subclass_of($subject, Model::class)) {
            $subject = new $subject;
        }

        if ($subject instanceof Model) {
            $queryRequest = $request
                ? QueryBuilderRequest::fromRequest($request)
                : app(QueryBuilderRequest::class);

            $subject = $subject::search($queryRequest->search());
        }

        /** @var static<T> $queryBuilder */
        $queryBuilder = new static($subject, $request);

        return $queryBuilder;
    }

    public function __call(string $name, array $arguments): mixed
    {
        $result = $this->forwardCallTo($this->subject, $name, $arguments);

        if ($result === $this->subject) {
            return $this;
        }

        return $result;
    }

    public function clone(): static
    {
        return clone $this;
    }

    public function __clone(): void
    {
        $this->subject = clone $this->subject;
    }

    public function __get(string $name): mixed
    {
        return $this->subject->{$name};
    }

    public function __set(string $name, mixed $value): void
    {
        $this->subject->{$name} = $value;
    }
}
