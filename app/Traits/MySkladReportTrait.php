<?php
namespace App\Traits;

use App\Models\Product;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait MySkladReportTrait
{

    //Генерация в бд
    public function newReport()
    {
        $product = new Product();
        $product->getProductsFromMySklad();
    }

    public function deletePreviousReport()
    {
        $directory = 'mysklad';

        DB::table('products')->truncate();
        DB::table('images')->truncate();

        Storage::deleteDirectory($directory);
    }

    //Запись в exel
    public function writeReport()
    {
        $product = new Product();
        $product->addProductsToSpreadSheet();
    }

    public function downloadImages($limit)
    {
        $product = new Product();
        $product->downloadImagesFromMySklad($limit);
    }

    public function deleteImgsFiles()
    {



    }

}