<?php

namespace App\Services;

use App\OfficeCodes;
use App\Facades\ParseClientService as ParseClient;
use cucxabeng\HtmlDom\HtmlDom;

class CodesParser
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function codes_parse()
    {
        $client = ParseClient::create_connection();
        $count = 0;
        do {
            $response = $client->request('GET', 'https://realt.by/sale/offices/?page=' . $count++);

            $html = $response->getBody();
            $dom = new HtmlDom($html);
            $price_found = $dom->find('span[class=price-byr]');
            $url_found = $dom->find('div[class=title] a[!class]');
            if ($url_found != null) {

                $position_array = $this->get_position_price($price_found);

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
    }

    /**
     * @param $price_found
     * @return array
     */
    private function get_position_price($price_found)
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
}
