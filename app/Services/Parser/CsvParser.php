<?php declare(strict_types = 1);

namespace App\Services\Parser;

use Illuminate\Support\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class CsvParser implements Parser
{
    private const HEADER_MAP = [
        'product_code' => 'code',
        'product_name' => 'name',
        'product_description' => 'description',
        'stock' => 'stock',
        'cost_in_gbp' => 'price',
        'discontinued' => 'discontinued_at',
    ];

    private const FIELD_CASTS = [
        'code' => 'string',
        'name' => 'string',
        'description' => 'string',
        'stock' => 'int',
        'price' => 'money',
        'discontinued_at' => 'date',
    ];

    public function parse(File $file): Collection
    {
        $content = explode(PHP_EOL, $file->getContent());
        $header = $this->formatHeader(array_shift($content));
        return collect($content)
            ->map(fn (string $line): array => $this->parseProduct($header, $line))
            ->filter(function (array $product) {
                return isset($product['name']) && $product['name'] !== '';
            });
    }

    private function parseRow(string $row): array
    {
        return explode(',', $row);
    }

    private function formatHeader(string $header): array
    {
        return collect($this->parseRow($header))
            ->map(static function (string $key): ?string {
                $str = Str::slug($key, '_');
                return array_key_exists($str, self::HEADER_MAP) ? self::HEADER_MAP[$str] : null;
            })
            ->filter()
            ->all();
    }

    private function parseProduct(array $header, string $line): array
    {
        $fields = array_pad($this->parseRow($line), count($header), null);
        $parsed = [];
        $needsAmending = false;

        foreach ($header as $key => $field) {
            if ($needsAmending) {
                $key = min($this->getNextHeaderKey($header, $field), count($fields) - 1);
            }

            $value = $fields[$key];

            if ($this->isSameType($field, $value)) {
                $parsed[$field] = $this->cast($field, $value);
                continue;
            }

            // This should be a field moved from another column (who would do that? *wink*),
            // let's put it back and amend the value for the current field. We'll also
            // set a flag for the upcoming fields so they reference the correct column from the CSV
            $previous = $header[$this->getPreviousHeaderKey($header, $field)];
            $current = $header[$this->getNextHeaderKey($header, $field)];
            $parsed[$previous] .= $this->cast($previous, $value);

            // At this point, we actually need the NEXT value in the array, as the CURRENT
            // value belongs to the PREVIOUS column
            $parsed[$field] = $this->cast($current, $fields[$key + 1]);

            // This flag will let the foreach know that the CURRENT value is actually the NEXT value
            $needsAmending = true;
        }

        return $parsed;
    }

    private function getPreviousHeaderKey(array $header, string $current): int
    {
        foreach ($header as $key => $value) {
            if ($current === $value) {
                return $key - 1;
            }
        }
    }

    private function getNextHeaderKey(array $header, string $current): int
    {
        foreach ($header as $key => $value) {
            if ($current === $value) {
                return $key + 1;
            }
        }
    }

    private function isSameType(string $field, mixed $value): bool
    {
        return match(self::FIELD_CASTS[$field]) {
            'string' => is_string($value),
            'int' => (is_numeric($value) && is_int((int)$value)) || $value === null || empty($value),
            'money' => $this->isMoney($value),
            'date' => $this->isBool($value) || $this->isDate($value),
            'default' => false,
        };
    }

    private function isMoney(mixed $value): bool
    {
        $value = $this->stripCurrency($value);
        return (is_numeric($value) && is_float((float)$value)) || $value === null;
    }

    private function stripCurrency(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $currencies = ['$', '£', '€', '¥'];

        foreach ($currencies as $currency) {
            if (str_contains($value, $currency)) {
                $value = str_replace($currency, '', $value);
            }
        }
        return $value;
    }

    private function isBool(mixed $value): bool
    {
        return is_bool($value)
            || empty($value)
            || $this->isTrue($value)
            || $this->isFalse($value);
    }

    private function isTrue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }
        return in_array(strtolower($value), [
            'y', 'yes', 'true', true,
        ]);
    }

    private function isFalse(mixed $value): bool
    {
        return in_array(strtolower($value), [
            'n', 'no', 'false', false, null,
        ]);
    }

    private function isDate(mixed $value): bool
    {
        try {
            new Carbon($value);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function toDate(mixed $value): ?Carbon
    {
        if ($this->isBool($value)) {
            return $this->isTrue($value) ? Carbon::now() : null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return Carbon::now();
        }
    }

    private function cast(string $field, mixed $value): mixed
    {
        return match(self::FIELD_CASTS[$field]) {
            'string' => (string)$value,
            'int' => intval($value),
            'money' => floatval($value),
            'date' => $this->toDate($value),
            'default' => null,
        };
    }
}
