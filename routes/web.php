<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\OfficeCodes;
use App\OfficeObjects;
use cucxabeng\HtmlDom\HtmlDom;
use Curl\Curl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/parse/codes', function () {
    $curl = setup_curl();

    $count = 0;
    do {
        $url = 'https://realt.by/sale/offices/?page=' . $count++;
        $curl->get($url);
        if ($curl->error) {
            $curl->close();
            break;
        } else {
            $html = $curl->response;
            $dom = new HtmlDom($html);
            $url_find = $dom->find('div[class=title] a[!class]');
            if ($url_find != null) {
                foreach ($url_find as $url) {
                    $url = $url->getAttribute('href');
                    $officeCode = new OfficeCodes;
                    $formatted_code = preg_replace("/[^0-9]/", '', $url);
                    if ($officeCode->where('code', $formatted_code)->first() == null) {
                        $officeCode->code = $formatted_code;
                        $officeCode->url = $url;
                        $officeCode->save();
                    }
                }
            } else {
                break;
            }
        }
    } while (true);

    $curl->close();
});

Route::get('/parse/offices', function () {
    $curl = setup_curl();

    $codes = OfficeCodes::all();
    foreach ($codes as $code) {
        $officeCode = $code->code;
        $url = $code->url;
        $curl->get($url);
        if ($curl->error) {
            print $curl->error_message;
            $curl->close();
        } else {
            $result = array();
            $html = $curl->response;
            $dom = new HtmlDom($html);

            $officeObject = new OfficeObjects;
            if ($officeObject->where('code', $officeCode)->first() == null) {
                $price_find = $dom->find('span[class=b14 price-byr]');
                $price = preg_replace("/(&nbsp;)/", '', $price_find[0]->plaintext);
                if (strripos($price, 'кв.м') === false) {
                    $formatted_price = preg_replace("/[^0-9]/", '', $price);
                    if ($formatted_price != null) {
                        $officeObject->price = $formatted_price;
                        $officeObject->code = $officeCode;

                        $table_dom = new HtmlDom();
                        $tables = $dom->find('table[class=table-zebra]');
                        for ($i = 0; $i < 4; $i++) {
                            $html = $table_dom->load($tables[$i]);
                            $lefts = $html->find('td[class=table-row-left]');
                            $left_array = array();
                            foreach ($lefts as $left) {
                                $left = preg_replace("/(&nbsp;)/", '', $left->plaintext);
                                $left = preg_replace("/(&mdash;)/", '-', $left);

                                $left_array[] = $left;
                            }
                            $rights = $html->find('td[class=table-row-right]');
                            $right_array = array();
                            foreach ($rights as $right) {
                                $right = preg_replace("/(&nbsp;)/", '', $right->plaintext);
                                $right = preg_replace("/(&mdash;)/", '-', $right);

                                $right_array[] = $right;
                            }
                            $result[] = json_encode(array_combine($left_array, $right_array));
                        }

                        $officeObject->contact = $result[0];
                        $officeObject->location = $result[1];
                        $officeObject->options = $result[2];
                        $officeObject->conditions = $result[3];

                        $officeObject->save();
                    }
                }
            }
        }
    };
    $curl->close();
});

Route::get('office/{code}', function ($code) {
    $officeObject = new OfficeObjects;
    $office = $officeObject->where('code', $code)->first();
    if ($office != null) {
        show_office($office);
    }
});

/**
 * @param $table
 * @return null|string|string[]
 */
function clear_table_from_tags($table)
{
    $current_table = $table;
    $current_table = preg_replace('/<td.+?>/', '<td>', $current_table);
    $current_table = preg_replace('/<tr.+?>/', '<tr>', $current_table);
    $current_table = preg_replace('/<div.+?>/', '<div>', $current_table);
    $current_table = preg_replace('/<a.+?>/', '<a>', $current_table);
    $current_table = preg_replace('/<table.+?>/', '<table>', $current_table);
    return $current_table;

}

/**
 * @return Curl
 * @throws ErrorException
 */
function setup_curl()
{
    set_time_limit(0);

    $referer = 'https://www.google.com/';

    $curl = new Curl;
    $curl->setUserAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36');
    $curl->setReferrer($referer);
    $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
    $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
    $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
    $curl->setOpt(CURLOPT_CONNECTTIMEOUT, false);
    $curl->setOpt(CURLOPT_HEADER, false);
    $curl->setOpt(CURLOPT_TIMEOUT, false);
    $curl->setOpt(CURLOPT_HEADER, false);

    return $curl;
}
