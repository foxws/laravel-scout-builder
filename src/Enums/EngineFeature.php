<?php

declare(strict_types=1);

namespace Foxws\ScoutBuilder\Enums;

enum EngineFeature: string
{
    case OperatorFilter = 'operator_filter';
    case FieldSort = 'field_sort';
}
