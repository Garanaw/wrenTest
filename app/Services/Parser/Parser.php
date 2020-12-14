<?php declare(strict_types = 1);

namespace App\Services\Parser;

use Illuminate\Http\File;
use Illuminate\Support\Collection;

interface Parser
{
    public function parse(File $data): Collection;
}
