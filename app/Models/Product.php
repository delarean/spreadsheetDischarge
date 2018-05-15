<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MoySklad\Entities\Reports\StockReport;
use MoySklad\MoySklad;
use MoySklad\Components\Specs\QuerySpecs\Reports\StockReportQuerySpecs;

class Product extends Model
{

    public $timestamps = false;
    protected $table = 'products';
    protected $guarded = ['id'];

    protected $sklad;

    private $password;
    private $login;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->login = env('MOY_SKLAD_LOGIN',false);
        $this->password = env('MOY_SKLAD_PASSWD',false);
    }

    //Получаем данные из моего склада и записываем в бд
    public function getProductsFromMySklad()
    {
        $this->sklad = MoySklad::getInstance($this->login, $this->password);

        $specs_arr = [
            'limit' => 100,
            'offset' => 0,
            'stockMode' => 'all',
        ];

        while(true){

            $specs = StockReportQuerySpecs::create($specs_arr);

            $report = StockReport::all($this->sklad,$specs);

            $response =  json_decode(json_encode($report), true);

            $resp_products = $response['rows'];

            if(empty($resp_products))
                break;

            foreach ($resp_products as $resp_product){

                $prod_id = $this->parseProductId($resp_product["meta"]["href"]);

                $price = ($resp_product['salePrice'])/100;

                if(gettype($price) !== "double")
                    $price = (double) $price.".00";

                $this->price = $price;
                $this->name = $resp_product['name'];
                $this->articul = $resp_product['article'] ?? ' ';

                $this->is_remained = $resp_product['quantity'] > 0 ? 1 : 0;



                if(isset($resp_product['image']))
                $this->image_href = $resp_product['image']['miniature']['href'];
                else $this->image_href = null;

                $colums = [
                    'name' => $this->name,
                    'articul' => $this->articul,
                    'is_remained' => $this->is_remained,
                    'price' => $this->price,
                    'image_download_href' => $this->image_href,
                    'product_id' => $prod_id,
                ];

                $columsArr = [
                    ['price', $colums['price'],],
                    ['name' , $colums['name'],],
                    ['articul' , $colums['articul'],],
                    ['is_remained' , $colums['is_remained'],]
                ];

                if(!$this->where($columsArr)->exists())
                    $this->create($colums);

            };

            $specs_arr['offset'] += $specs_arr['limit'];

        }

    }

    //Парсим id товара из ссылки
    private function parseProductId($url)
    {

        $url_parts = explode('/',$url);

        $dirt_url = $url_parts[8];

        if(strrpos($dirt_url,'?')){
            $url_parts = explode('?',$dirt_url);
            $product_id = $url_parts[0];
        }
        else
            $product_id = $dirt_url;


        return $product_id;

    }

    public function addProductsToSpreadSheet()
    {
        $spreadsheetId = '1wKCUfq0Zr_ItIa4WzUMwLERewdCFYlIZZ9ETHo-q1Vk';

        $service = $this->getSpreadSheetService();

        $products = $this->all();

        $values = [];

        $range_num = $products->count() + 1;

        $range = "A2:E".$range_num;

        foreach ($products as $product){

            $remained = $product->is_remained == '1' ? 'Да' : "Нет";

            $image = DB::table('images')
                ->select('image_href')
                ->where('articul',$product->articul)
                ->get()
                ->first();

            if(isset($image)){

                $img_href = $image->image_href;

                $img_func = "=image(\"".$img_href."\"; 3)";

            }
            else
                $img_func = " ";

            $values[] = [
                $product->articul,
                $product->name,
                $remained,
                $product->price,
                $img_func,
            ];

        }

        $this->insertSpreadSheet($service,$spreadsheetId,$range,$values);

    }

    //@return Google_Service_Sheets $service
    public function getSpreadSheetService()
    {

        $path = env('APP_PATH',false);

        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$path.'/service-account.json');

        $client = new \Google_Client();
        $client->useApplicationDefaultCredentials();

        $client->addScope("https://www.googleapis.com/auth/spreadsheets");

        $service = new \Google_Service_Sheets($client);

        return $service;

    }

    public function insertSpreadSheet($service,$spreadsheetId,$range,$values)
    {

        $valueInputOption = 'USER_ENTERED';

        $data = [];
        $data[] = new \Google_Service_Sheets_ValueRange([
            'range' => $range,
            'values' => $values
        ]);

        $body = new \Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => $valueInputOption,
            'data' => $data
        ]);

        $result = $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);

    }

    public function deleteSpreadSheet($service,$spreadsheetId,$range)
    {
        $rangeParts = explode(':',$range);

        $firstNum = $rangeParts[0][1];
        $secondNum = $rangeParts[1][1];

        $count = $firstNum - $secondNum;

        if($count < 0)
            $count *= -1;

        $values = [];

        for($i = 0;$i < $count;$i++){

            $values[] = [
              ' ',' ',' ',' '
            ];

        }

        $range = "A".$firstNum.":D".$secondNum;

        $this->insertSpreadSheet($service,$spreadsheetId,$range,$values);

    }

    private function downloadImageFromUrl($download_url)
    {

            $curl = curl_init();

            $upl_file_name = 'upload_img.png';
            $upl_fp = App::storagePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public';
            $upl_fp .= DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$upl_file_name;

            //$fp = fopen($upl_fp, 'wb');
            curl_setopt($curl, CURLOPT_URL, $download_url);
            //curl_setopt($curl, CURLOPT_FILE, $fp);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: Basic '.base64_encode($this->login.':'.$this->password),
            ]);
            curl_exec($curl);
            $info_arr = curl_getinfo($curl);
            curl_close($curl);

            $redirect_url = $info_arr['redirect_url'];

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $redirect_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        $resp = curl_exec($curl);
        curl_close($curl);

        $fp = fopen($upl_fp, 'wb');

        fwrite($fp, $resp);

        fclose($fp);

            return $upl_fp;

    }

    private function uploadImage($image_path)
    {
        $file = new File($image_path);

        $path = Storage::putFile('mysklad/images', $file);

        return Storage::url($path);

    }

    //Загружаем изображения из моего склада на сервер и сохраняем url
    public function downloadImagesFromMySklad($limit = 500)
    {

        $sql_stat = 'SELECT id,image_download_href,product_id,is_downloaded,articul
                     FROM products
                     WHERE image_download_href IS NOT NULL
                     AND is_downloaded=0
                     AND articul NOT IN(SELECT articul FROM images)
                     LIMIT :lim';

        $products = DB::select($sql_stat,
            [
                'lim' => $limit
            ]);

        foreach ($products as $product){

            if(DB::table('images')->where(['articul' => $product->articul])->exists())
                continue;

            $image_bin = $this->downloadImageFromUrl($product->image_download_href);

            $img_path = $this->uploadImage($image_bin);

            //Записываем в бд
            DB::transaction(function () use ($product,$img_path){

                DB::table('products')
                    ->where([
                        ['articul',$product->articul]
                    ])
                    ->update(['is_downloaded' => 1]);

                DB::table('images')->insert(
                    [
                        'image_href' => $img_path,
                        'articul' => $product->articul,
                    ]
                );

            });
        }
    }

    

}
