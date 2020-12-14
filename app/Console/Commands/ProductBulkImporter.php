<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Parser\Parser;
use Illuminate\Console\Command;
use Illuminate\Http\File;
use Symfony\Component\Console\Input\InputArgument;

class ProductBulkImporter extends Command
{
    protected $signature = 'product:bulkImport {file}';

    protected $description = 'Inserts products from a CSV file into the database';

    private Parser $parser;

    public function __construct(Parser $parser)
    {
        parent::__construct();
        $this->parser = $parser;
    }

    public function handle()
    {
        $filePath = $this->argument('file');
        $file = new File($filePath);
        $data = $this->parser->parse($file);

        Product::upsert($data->toArray(), ['code']);

        return 0;
    }

    protected function getArguments()
    {
        return [
            ['file', InputArgument::REQUIRED, "File path"],
        ];
    }
}
