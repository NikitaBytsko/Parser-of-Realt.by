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
use GuzzleHttp\Client;
use cucxabeng\HtmlDom\HtmlDom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/parse/codes', function () {
    $client = set_up_client();
    $count = 0;
    do {
        $response = $client->request('GET', 'https://realt.by/sale/offices/?page=' . $count++);

        $html = $response->getBody();
        $dom = new HtmlDom($html);
        $price_found = $dom->find('span[class=price-byr]');
        $url_found = $dom->find('div[class=title] a[!class]');
        if ($url_found != null) {

            $position_array = get_position_price($price_found);

            $position_url = 0;
            foreach ($url_found as $url) {
                $url = $url->getAttribute('href');
                $officeCode = new OfficeCodes;
                $formatted_code = preg_replace("/[^0-9]/", '', $url);
                if ($officeCode->where('code', $formatted_code)->first() == null && in_array($position_url++, $position_array)) {
                    $officeCode->code = $formatted_code;
                    $officeCode->url = $url;
                    $officeCode->save();
                }
            }
        } else {
            break;
        }
    } while (true);
});

Route::get('/parse/offices', function () {
    $client = set_up_client();

    $offices = OfficeCodes::all();
    foreach ($offices as $office) {
        $url = $office->url;
        $code = $office->code;
        $response = $client->request('GET', $url);
        $html = $response->getBody();

        $dom = new HtmlDom($html);

        $officeObject = new OfficeObjects;
        if ($officeObject->where('code', $code)->first() == null) {
            parse_office_tables($dom, $code, $officeObject);
        }
    };
});

Route::get('office/{code}', function ($code) {
    $officeObject = new OfficeObjects;
    $office = $officeObject->where('code', $code)->first();
    if ($office != null) {
        show_decoded_json_office($office);
    }
});

/**
 * @param $office
 */
function show_decoded_json_office($office)
{
    dump(json_decode($office->contact));
    dump(json_decode($office->location));
    dump(json_decode($office->options));
    dump(json_decode($office->conditions));
}


/**
 * @param $price_found
 * @return array
 */
function get_position_price($price_found)
{
    $prices = $price_found;
    $position_price = 0;
    $position_array = array();

    foreach ($prices as $price) {
        $price = preg_replace("/(&nbsp;)/", '', $price->plaintext);
        if (strripos($price, 'кв.м') === false && preg_match("/[^0-9]/", $price) != false) {
            $formatted_price = preg_replace("/[^0-9]/", '', $price);
            if ($formatted_price != null) {
                $position_array[] = $position_price++;
            } else {
                $position_price++;
            }
        } else {
            $position_price++;
        }
    }
    return $position_array;
}

/**
 * @param $dom_parameter
 * @param $code_parameter
 * @param $office_parameter
 */
function parse_office_tables($dom_parameter, $code_parameter, $office_parameter)
{
    $dom = $dom_parameter;
    $code = $code_parameter;
    $office = $office_parameter;

    $price_found = $dom->find('span[class=b14 price-byr]');
    $price = preg_replace("/(&nbsp;)/", '', $price_found[0]->plaintext);

    if (strripos($price, 'кв.м') === false) {

        if (strpos($price, '&mdash;')) {
            $array_price = explode('&mdash;', $price);
            $price = array_sum($array_price) / count($array_price);
        }
        $formatted_price = preg_replace("/[^0-9]/", '', $price);
        if ($formatted_price != null) {

            $table_dom = new HtmlDom();
            $tables_found = $dom->find('table[class=table-zebra]');

            $result = get_office_tables($table_dom, $tables_found);

            $contact = formatting_contact_data($result[0]);
            $location = formatting_location_data($result[1]);
            $options = formatting_options_data($result[2]);
            $conditions = formatting_conditions_data($result[3]);

            $office->code = $code;
            $office->price = $formatted_price;
            $office->contact = $contact;
            $office->location = $location;
            $office->options = $options;
            $office->conditions = $conditions;

            $office->save();
        }
    }
}

/**
 * @param $contact_data
 * @return mixed
 */
function formatting_contact_data($contact_data)
{
    $contact = json_decode($contact_data);
    $formatted_contact_numbers = preg_replace('/[^0-9\+]/', '', $contact->Телефоны);
    $contact_numbers = explode("+", $formatted_contact_numbers);
    $contact->{'Телефоны'} = array_slice($contact_numbers, 1);

    return json_encode($contact);
}

/**
 * @param $location_data
 * @return mixed
 */
function formatting_location_data($location_data)
{
    $location = json_decode($location_data);

    return json_encode($location);
}

/**
 * @param $options_data
 * @return mixed
 */
function formatting_options_data($options_data)
{
    $options = json_decode($options_data);

    return json_encode($options);
}

/**
 * @param $conditions_data
 * @return mixed
 */
function formatting_conditions_data($conditions_data)
{
    $conditions = json_decode($conditions_data);
    $price = $conditions->{'Ориентировочная стоимость эквивалентна'};

    $array = explode(" ", $price);
    $array = array_unique($array);
    $price = implode(' ', $array);

//    if (strpos($price, '&mdash;')) {
//        $array_price = explode('&mdash;', $price);
//        $price = array_sum($array_price) / count($array_price);
//    }

    $formatted_price = preg_replace("/[^0-9]/", '', $price);
    $conditions->{'Ориентировочная стоимость эквивалентна'} = $formatted_price;

    return json_encode($conditions);
}


/**
 * @param $table_dom
 * @param $tables_found
 * @return array
 */
function get_office_tables($table_dom, $tables_found)
{
    $dom = $table_dom;
    $tables = $tables_found;
    $result = array();

    foreach ($tables as $table) {
        $html = $dom->load($table);

        $lefts = $html->find('td[class=table-row-left]');
        $left_array = get_table_column($lefts);

        $rights = $html->find('td[class=table-row-right]');
        $right_array = get_table_column($rights);

        $combined_array = array_combine($left_array, $right_array);

        if (array_key_exists(null, $combined_array)) {
            array_pop($combined_array);
        }

        $result[] = json_encode($combined_array);
    }

    return $result;
}

/**
 * @param $values
 * @return array
 */
function get_table_column($values)
{
    $array = array();

    foreach ($values as $value) {
        $value = preg_replace("/(&nbsp;)/", '', $value->plaintext);
        $value = preg_replace("/(&mdash;)/", '-', $value);

        $array[] = $value;
    }

    return $array;
}

/**
 * @return Client
 */
function set_up_client()
{
    set_time_limit(0);

    $accept = '*/*';
    $referer = 'https://www.google.com/';
    $content_type = 'application/x-www-form-urlencoded';
    $accept_language = 'en-US,en;q=0.8,hi;q=0.6,und;q=0.4';
    $user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36';

    $headers = [
        'Accept' => $accept,
        'Referer' => $referer,
        'User-Agent' => $user_agent,
        'Content-Type' => $content_type,
        'Accept-Language' => $accept_language,
    ];

    $client = new Client([
        'timeout' => 0,
        'verify' => false,
        'headers' => $headers,
        'connect_timeout' => 0,
    ]);

    return $client;
}
