<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceKindEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Str;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mtnMomo = new Service;
        $mtnMomo->name = "MTN Mobile Money";
        $mtnMomo->description = "Mobile money payments";
        $mtnMomo->image = "mtn-momo.png";
        $mtnMomo->slug = Str::slug($mtnMomo->name);
        $mtnMomo->kind = ServiceKindEnum::payment->value;
        $mtnMomo->uuid = Uuid::uuid4()->toString();
        // $mtnMomo->provider_id_1 = '20053';
        // $mtnMomo->provider_id_2 = 'CM_MTN_VTU_CASHOUT_OP';
        $mtnMomo->provider_id_1 = 'CHANNEL_TEST';
        $mtnMomo->provider_id_2 = '';
        $mtnMomo->enabled = true;
        $mtnMomo->public = false;
        $mtnMomo->min_amount = 10;
        $mtnMomo->max_amount = 50000;
        $mtnMomo->form_input_label = "Numéro MTN Mobile Money";
        $mtnMomo->form_input_placeholder = "ex: +237650675795";
        $mtnMomo->form_input_regex = "((|(0{2}))?237)?((67|650|651|652|653|654|680|681|682|683)([0-9]{6,7}))$";
        $mtnMomo->provider = "pg";
        $mtnMomo->save();

        $mtnMomoDebit = new Product;
        $mtnMomoDebit->service_id = $mtnMomo->id;
        $mtnMomoDebit->color = "bg-black-900";
        $mtnMomoDebit->name = "Collecte MTN Mobile Money";
        $mtnMomoDebit->default = true;
        // $mtnMomoDebit->provider_id_1 = '20053';
        // $mtnMomoDebit->provider_id_2 = 'CM_MTN_VTU_CASHOUT_OP';
        $mtnMomoDebit->provider_id_1 = 'CHANNEL_TEST';
        $mtnMomoDebit->provider_id_2 = '';
        $mtnMomoDebit->description = "Collecte MTN Mobile Money";
        $mtnMomoDebit->uuid = Uuid::uuid4();
        $mtnMomoDebit->slug = Str::slug("mtn-collect");
        $mtnMomoDebit->fixed_price = false;
        $mtnMomoDebit->enabled = true;
        $mtnMomoDebit->save();

        $orangeMoney = new Service;
        $orangeMoney->name = "Orange Money";
        $orangeMoney->description = "Paiements orange money";
        $orangeMoney->image = "orange-money.png";
        $orangeMoney->slug = Str::slug($orangeMoney->name);
        $orangeMoney->kind = ServiceKindEnum::payment->value;
        $orangeMoney->uuid = Uuid::uuid4()->toString();
        $orangeMoney->provider_id_1 = '20053';
        $orangeMoney->provider_id_2 = 'CM_MTN_VTU_CASHOUT_OP';
        $orangeMoney->enabled = true;
        $orangeMoney->public = false;
        $orangeMoney->min_amount = 10;
        $orangeMoney->max_amount = 50000;
        $orangeMoney->form_input_label = "Numéro Orange Money";
        $orangeMoney->form_input_placeholder = "ex: +23791080200";
        $orangeMoney->form_input_regex = "((|(0{2}))?237)?((69|655|656|657|658|659)([0-9]{6,7}))$";
        $orangeMoney->provider = "pg";
        $orangeMoney->save();

        $orangeMoneyDebit = new Product;
        $orangeMoneyDebit->service_id = $orangeMoney->id;
        $orangeMoneyDebit->color = "bg-black-900";
        $orangeMoneyDebit->name = "Collecte Orange Money";
        $orangeMoneyDebit->default = true;
        $orangeMoneyDebit->provider_id_1 = '50053';
        $orangeMoneyDebit->provider_id_2 = '900402';
        $orangeMoneyDebit->description = "Collecte Orange Money";
        $orangeMoneyDebit->uuid = Uuid::uuid4();
        $orangeMoneyDebit->slug = Str::slug("orange-collect");
        $orangeMoneyDebit->fixed_price = false;
        $orangeMoneyDebit->enabled = true;
        $orangeMoneyDebit->save();

        $product = new Product;
        $product->service_id = $mtnMomo->id;
        $product->color = "yellow";
        $product->name = "Paiement MTN Mobile Money";
        $product->description = $product->name;
        $product->uuid = Uuid::uuid4();
        $product->slug = Str::slug($product->name);
        $product->fixed_price = false;
        $product->enabled = true;
        $product->save();


        $canalPlus = new Service;
        $canalPlus->name = "Canal+";
        $canalPlus->image = "canal-plus.png";
        $canalPlus->slug = Str::slug($canalPlus->name);
        $canalPlus->kind = ServiceKindEnum::bill->value;
        $canalPlus->uuid = Uuid::uuid4()->toString();
        $canalPlus->form_input_label = "Numéro de décodeur";
        $canalPlus->form_input_placeholder = "10 chiffres, ex: 12083839393039";
        $canalPlus->form_input_regex = "^[0-9]{10}$";
        $canalPlus->description = "Abonnement et réabonnement canal plus";
        $canalPlus->provider_id_1 = '20053';
        $canalPlus->provider_id_2 = 'CM_MTN_VTU_CASHOUT_OP';
        $canalPlus->enabled = true;
        $canalPlus->public = true;
        $canalPlus->provider = "pg";
        $canalPlus->save();

        $canalPlusProducts = [
            [
                "name" => "ACCESS",
                "description" => "Plus de 245 chaînes, radios et services + MyCanal",
                "price" => "5000",
                "color" => "gray-800"
            ],
            [
                "name" => "EVASION",
                "description" => "Plus de 280 chaînes, radios et services + MyCanal",
                "price" => 10000,
                "color" => "gray-800"
            ],
            [
                "name" => "ESSENTIEL+",
                "description" => "Plus de 200 chaînes, radios et services+ MyCanal + les chaine",
                "price" => 13000,
                "color" => "gray-600"
            ],
            [
                "name" => "ACCESS+",
                "description" => "Plus de 200 chaînes, radios et services+ MyCanal + les chaine",
                "price" => 15000,
                "color" => "gray-600"
            ],
            [
                "name" => "EVASION+",
                "description" => "Plus de 310 chaînes, radios et services + MyCanal + les chaine",
                "price" => 20000,
                "color" => "gray-600"
            ],
            [
                "name" => "TOUT CANAL+",
                "description" => "Plus de 320 chaînes, radios et services radios et services + MyCanal + les chaine",
                "price" => 40000,
                "color" => "gray-900"
            ]
        ];
        foreach ($canalPlusProducts as $data) {
            $product = new Product;
            $product->service_id = $canalPlus->id;
            $product->color = $data["color"];
            $product->name = $data["name"];
            $product->description = $data["description"];
            $product->uuid = Uuid::uuid4();
            $product->slug = Str::slug($data["name"]);
            $product->fixed_price = true;
            $product->price = $data["price"];
            $product->enabled = true;
            $product->save();
        }

        $blue = new Service;
        $blue->name = "CAMTEL";
        $blue->image = "blue.png";
        $blue->slug = Str::slug($blue->name);
        $blue->kind = ServiceKindEnum::bill->value;
        $blue->uuid = Uuid::uuid4()->toString();
        $blue->min_amount = 10;
        $blue->max_amount = 50000;
        $blue->form_input_label = "Numéro camtel";
        $blue->form_input_placeholder = "Numéro CAMTEL, ex: 6XXXXXXXX";
        $blue->form_input_regex = "^[0-9]{9}$";
        $blue->description = "Abonnement et crédit de communication camtel";
        $blue->provider_id_1 = '20053';
        $blue->provider_id_2 = 'CM_MTN_VTU_CASHOUT_OP';
        $blue->enabled = true;
        $blue->public = true;
        $blue->provider = "pg";
        $blue->save();

        $blueAirtime = new Product;
        $blueAirtime->service_id = $blue->id;
        $blueAirtime->color = 'bg-black-900';
        $blueAirtime->name = "Crédit Blue/Camtel";
        $blueAirtime->description = "Acheter de crédit de communication";
        $blueAirtime->uuid = Uuid::uuid4();
        $blueAirtime->slug = 'credit';
        $blueAirtime->provider_id_1 = 'CHANNEL_CAMTEL';
        $blueAirtime->provider_id_2 = '';
        $blueAirtime->fixed_price = false;
        $blueAirtime->price = null;
        $blueAirtime->enabled = true;
        $blueAirtime->save();

        $camtelProducts = [
            [
                "name" => "Blue GO S",
                "provider_id_2" => "BLUE_GO_S",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 2_000,
                "description" => "300 Mo/Jour",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue GO M",
                "provider_id_2" => "BLUE_GO_M",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 5_000,
                "description" => "850 Mo/Jour",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue GO L",
                "provider_id_2" => "BLUE_GO_L",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 10_000,
                "description" => "2 Go/Jour",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue GO XL",
                "provider_id_2" => "BLUE_GO_XL",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 20_000,
                "description" => "4.5 Go/Jour",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue GO XXL",
                "provider_id_2" => "BLUE_GO_XXL",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 35_000,
                "description" => "8 Go/Jour",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue GO Plus S",
                "provider_id_2" => "BLUE_GO_PLUS_S",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 15_000,
                "description" => "60 Go/30 jours",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue GO Plus M",
                "provider_id_2" => "BLUE_GO_PLUS_M",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 25_000,
                "description" => "135 Go/30 jours",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue GO Plus L",
                "provider_id_2" => "BLUE_GO_PLUS_L",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 40_000,
                "description" => "240 Go/30 jours",
                "color" => "bg-gray-800"
            ],

            [
                "name" => "Blue Night",
                "provider_id_2" => "BLUE_NIGHT",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 250,
                "description" => "5 Go/22h-06h",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue One S",
                "provider_id_2" => "BLUE_ONE_S",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 2_000,
                "description" => "Whatsapp, Facebook Télégram, Twitter + 4Go / 1 semaine",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue One M",
                "provider_id_2" => "BLUE_ONE_M",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 3_000,
                "description" => "Whatsapp, Facebook Télégram, Twitter + 5Go / 30 jours",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue One L",
                "provider_id_2" => "BLUE_ONE_L",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 5_000,
                "description" => "Whatsapp, Facebook, Télégram, Twitter, Instagram + 8 Go / 30 jours",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue One XL",
                "provider_id_2" => "BLUE_ONE_XL",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 10_000,
                "description" => "Whatsapp, Facebook, Télégram, Twitter, Instagram , Youtube + 20 Go / 30 jours",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue One XXL",
                "provider_id_2" => "BLUE_ONE_XXL",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 20_000,
                "description" => "Whatsapp, Facebook, Télégram, Twitter, Instagram , Youtube, Netflix + 50 Go / 30 jours",
                "color" => "bg-gray-800"
            ],

            [
                "name" => "Blue Mo S",
                "provider_id_2" => "BLUE_MO_S",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 100,
                "description" => "1 Go / 3 heures",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue Mo M",
                "provider_id_2" => "BLUE_MO_M",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 300,
                "description" => "1.5 Go / Jour",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue Mo L",
                "provider_id_2" => "BLUE_MO_L",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 500,
                "description" => "2Go / 2 Jours",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue Mo XL",
                "provider_id_2" => "BLUE_MO_XL",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 1000,
                "description" => "2Go / 7 jours",
                "color" => "bg-gray-800"
            ],
            [
                "name" => "Blue Mo XXL",
                "provider_id_2" => "BLUE_MO_XXL",
                "provider_id_1" => "CHANNEL_CAMTEL",
                "price" => 5000,
                "description" => "10Go / 1 month",
                "color" => "bg-gray-800"
            ],
        ];

        foreach ($camtelProducts as $data) {
            $product = new Product;
            $product->service_id = $blue->id;
            $product->color = $data["color"];
            $product->name = $data["name"];
            $product->description = $data["description"];
            $product->provider_id_1 = $data["provider_id_1"];
            $product->provider_id_2 = $data["provider_id_2"];
            $product->uuid = Uuid::uuid4();
            $product->slug = Str::slug($data["name"]);
            $product->fixed_price = true;
            $product->price = $data["price"];
            $product->enabled = true;
            $product->save();
        }

    }
}