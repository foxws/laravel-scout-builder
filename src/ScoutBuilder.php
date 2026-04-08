<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Foxws\ScoutBuilder\Concerns\FiltersQuery;
use Foxws\ScoutBuilder\Concerns\IncludesQuery;
use Foxws\ScoutBuilder\Concerns\SortsQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\ForwardsCalls;
use Laravel\Scout\Builder as LaravelScoutBuilder;

/**
 * @template TModel of Model
 *
 * @mixin LaravelScoutBuilder<TModel>
 */
class ScoutBuilder
{
    use FiltersQuery;
    use ForwardsCalls;
    use IncludesQuery;
    use SortsQuery;

    protected ScoutBuilderRequest $request;

    public function __construct(
        protected LaravelScoutBuilder $subject,
        ?Request $request = null,
    ) {
        $this->request = $request
            ? ScoutBuilderRequest::fromRequest($request)
            : app(ScoutBuilderRequest::class);
    }

    public function getScoutBuilder(): LaravelScoutBuilder
    {
        return $this->subject;
    }

    public function getSubject(): LaravelScoutBuilder
    {
        return $this->subject;
    }

    /**
     * @template T of Model
     *
     * @param  LaravelScoutBuilder<T>|T|class-string<T>  $subject
     * @return static<T>
     */
    public static function for(LaravelScoutBuilder|Model|string $subject, ?Request $request = null): static
    {
        if (is_string($subject) && is_subclass_of($subject, Model::class)) {
            $subject = new $subject;
        }

        if ($subject instanceof Model) {
            $queryRequest = $request
                ? ScoutBuilderRequest::fromRequest($request)
                : app(ScoutBuilderRequest::class);

            $subject = $subject::search($queryRequest->search());
        }

        /** @var static<T> $scoutBuilder */
        $scoutBuilder = new static($subject, $request);

        return $scoutBuilder;
    }

    public function __call(string $name, array $arguments): mixed
    {
        $result = $this->forwardCallTo($this->subject, $name, $arguments);

        if ($result === $this->subject) {
            return $this;
        }

        return $result;
    }

    public function cloneBuilder(): static
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
