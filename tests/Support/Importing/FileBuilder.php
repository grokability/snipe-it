<?php

declare(strict_types=1);

namespace Tests\Support\Importing;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use League\Csv\Reader;
use OutOfBoundsException;

/**
 * @template Row of array
 */
abstract class FileBuilder
{
    /**
     * The import file rows.
     *
     * @var Collection<Row>
     */
    protected Collection $rows;

    /**
     * Define the builders default row.
     *
     * @return Row
     */
    abstract public function definition();

    /**
     * @param array<Row> $rows
     */
    public function __construct(array $rows = [])
    {
        $this->rows = new Collection($rows);
    }

    /**
     * Get a new file builder instance.
     *
     * @param Row $attributes
     *
     * @return static
     */
    public static function new(array $attributes = [])
    {
        $instance = new static;

        return $instance->push($instance->definition())->replace($attributes);
    }

    /**
     * Get a new file builder instance from an import file.
     *
     * @return static
     */
    public static function fromFile(string $filepath)
    {
        $instance = new static;

        $reader = Reader::createFromPath($filepath);
        $importFileHeaders = $reader->first();
        $dictionary = array_flip($instance->getDictionary());

        foreach ($reader->getRecords() as $key => $record) {
            $row = [];

            //Skip header.
            if ($key === 0) {
                continue;
            }

            foreach ($record as $index => $value) {
                $columnNameInImportFile = $importFileHeaders[$index];

                //Try to map the value to a dictionary  or use the file's
                //column if the key is not defined in the dictionary.
                $row[$dictionary[$columnNameInImportFile] ?? $columnNameInImportFile] = $value;
            }

            $instance->push($row);
        }

        return $instance;
    }

    /**
     * Get a new builder instance for the given number of rows.
     *
     * @return static
     */
    public static function times(int $amountOfRows = 1)
    {
        $instance = new static;

        for ($i = 1; $i <= $amountOfRows; $i++) {
            $instance->push($instance->definition());
        }

        return $instance;
    }

    /**
     * The the dictionary for mapping row keys to the corresponding import file headers.
     *
     * @return array<string,string>
     */
    protected function getDictionary(): array
    {
        return [];
    }

    /**
     * Add a new row.
     *
     * @param Row $row
     *
     * @return $this
     */
    public function push(array $row)
    {
        if (!empty($row)) {
            $this->rows->push($row);
        }

        return $this;
    }

    /**
     * Pluck an array of values from the rows.
     */
    public function pluck(string $key): array
    {
        return $this->rows->pluck($key)->all();
    }

    /**
     * Replace the keys in each row with the values of the given replacement if they exist.
     *
     * @param array<Row> $replacement
     *
     * @return $this
     */
    public function replace(array $replacement)
    {
        $this->rows = $this->rows->map(function (array $row) use ($replacement) {
            foreach ($replacement as $key => $value) {
                if (!array_key_exists($key, $row)) {
                    continue;
                }

                $row[$key] = $value;
            }

            return $row;
        });

        return $this;
    }

    /**
     * Remove the the given keys from all rows.
     *
     * @param string|array<string> $keys
     *
     * @return $this
     */
    public function forget(array|string $keys)
    {
        $keys = (array) $keys;

        $this->rows = $this->rows->map(function (array $row) use ($keys) {
            foreach ($keys as $key) {
                unset($row[$key]);
            }

            return $row;
        });

        return $this;
    }

    public function toCsv(): array
    {
        if ($this->rows->isEmpty()) {
            return [];
        }

        $headers = [];
        $rows = $this->rows;
        $dictionary = $this->getDictionary();

        foreach (array_keys($rows->first()) as $key) {
            $headers[] = $dictionary[$key] ?? $key;
        }

        return $rows
            ->map(fn (array $row) => array_values(array_combine($headers, $row)))
            ->prepend($headers)
            ->all();
    }

    /**
     * Save the rows to the imports folder as a csv file.
     *
     * @return string The filename.
     */
    public function saveToImportsDirectory(?string $filename = null, ?string $locale = null): string
    {
        $filename ??= Str::random(40) . '.csv';

        try {
            $stream = fopen(config('app.private_uploads') . "/imports/{$filename}", 'w');

            foreach ($this->toCsv() as $row) {
                if ($locale) {
                    $newrow = [];
                    foreach ($row as $index => $cell) {
                        $newrow[$index] = iconv('utf-8', $locale, (string) $cell);
                    }
                    $row = $newrow;
                }
                fputcsv($stream, $row);
            }
            return $filename;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /**
     * Get the first row of the import file.
     *
     * @throws OutOfBoundsException
     *
     * @return Row
     */
    public function firstRow(): array
    {
        return $this->rows->first(null, fn () => throw new OutOfBoundsException('Could not retrieve row from collection.'));
    }

    /**
     * Get the all the rows of the import file.
     *
     * @return array<Row>
     */
    public function all(): array
    {
        return $this->rows->all();
    }
}
