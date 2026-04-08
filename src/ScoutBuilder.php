<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder;

use Foxws\ScoutBuilder\Concerns\FiltersQuery;
use Foxws\ScoutBuilder\Concerns\IncludesQuery;
use Foxws\ScoutBuilder\Concerns\SortsQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Traits\ForwardsCalls;
use Laravel\Scout\Builder;

/**
 * @template TModel of Model
 *
 * @mixin Builder<TModel>
 */
class ScoutBuilder
{
    use FiltersQuery;
    use ForwardsCalls;
    use IncludesQuery;
    use SortsQuery;

    protected ScoutBuilderRequest $request;

    public function __construct(
        protected Builder $subject,
        ?Request $request = null,
    ) {
        $this->request = $request
            ? ScoutBuilderRequest::fromRequest($request)
            : app(ScoutBuilderRequest::class);
    }

    public function getScoutBuilder(): Builder
    {
        return $this->subject;
    }

    public function getSubject(): Builder
    {
        return $this->subject;
    }

    /**
     * @template T of Model
     *
     * @param  Builder<T>|T|class-string<T>  $subject
     * @return static<T>
     */
    public static function for(Builder|Model|string $subject, ?Request $request = null): static
    {
        if (is_string($subject) && is_subclass_of($subject, Model::class)) {
            $subject = new $subject;
        }

        if ($subject instanceof Model) {
            $queryRequest = $request
                ? ScoutBuilderRequest::fromRequest($request)
                : app(ScoutBuilderRequest::class);

            /** @phpstan-ignore-next-line */
            $subject = $subject::search($queryRequest->search());
        }

        /** @var static<T> $scoutBuilder */
        $scoutBuilder = new static($subject, $request);

        return $scoutBuilder;
    }

    public function jsonPaginate(?int $maxResults = null, ?int $defaultSize = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->subject->paginate(
            $this->getPaginationSize($maxResults, $defaultSize),
            $this->getPaginationPageName(),
            $this->request->pageNumber(),
        );
    }

    public function jsonSimplePaginate(?int $maxResults = null, ?int $defaultSize = null): \Illuminate\Contracts\Pagination\Paginator
    {
        return $this->subject->simplePaginate(
            $this->getPaginationSize($maxResults, $defaultSize),
            $this->getPaginationPageName(),
            $this->request->pageNumber(),
        );
    }

    protected function getPaginationSize(?int $maxResults, ?int $defaultSize): int
    {
        $maxResults ??= (int) Config::get('scout-builder.pagination.max_size', 30);
        $defaultSize ??= (int) Config::get('scout-builder.pagination.default_size', 30);

        return min($this->request->pageSize() ?: $defaultSize, $maxResults);
    }

    protected function getPaginationPageName(): string
    {
        $paginationParameter = (string) Config::get('scout-builder.pagination.pagination_parameter', 'page');
        $numberParameter = (string) Config::get('scout-builder.pagination.number_parameter', 'number');

        return "{$paginationParameter}[{$numberParameter}]";
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
