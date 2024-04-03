<?php

namespace App\Console\Commands;

use App\Models\Option;
use App\Models\Product;
use App\Models\Service;
use App\Services\FujisatService;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

class ImportFujisatCanalPlusProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-fujisat-canal-plus-products {serviceId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import fujisat canal plus product';

    /**
     * Execute the console command.
     */
    public function handle(FujisatService $fujisatService)
    {
        $serviceId = $this->argument('serviceId');
        $service = Service::find($serviceId);

        if (!$service) {
            $this->error(sprintf('service [%s] not found', $serviceId));
            return self::SUCCESS;
        }

        if (!$this->confirm(sprintf('Continue with service [%s]', $service->name))) {
            return self::SUCCESS;
        }

        $products = $fujisatService->getProducts($service);
        $this->table(
            ['Product', 'code', 'price', 'options'],
            array_map(
                fn(array $data) => [
                    $data['product']->name,
                    $data['product']->description,
                    $data['product']->formatted_price,
                    collect($data['options'])->pluck('name')->join(', ')
                ]
                ,
                $products
            )
        );

        if (!$this->confirm(sprintf('Continue? all the previous product of the service with a fixed price will be deleted', $service->name))) {
            return self::SUCCESS;
        }

        $service->products()->where('fixed_price', true)->delete();
        $service->options()->delete();

        foreach ($products as $productData) {
            /** @var Product $product */
            $product = $productData['product'];
            $product->color = "gray-800";
            $product->enabled = true;
            $product->fixed_price = true;
            $product->uuid = Uuid::uuid4()->toString();
            $product->save();

            $options = [];
            foreach ($productData['options'] as $opt) {
                $option = Option::findByCode($opt->code);
                if (!$option) {
                    $opt->service()->associate($service);
                    $opt->enabled = true;
                    $opt->save();
                    $option = $opt;
                }
                $options[] = $option;
            }

            $product->options()->sync(collect($options)->pluck('id'));

        }

        $this->info('OK');

        return self::SUCCESS;
    }
}
