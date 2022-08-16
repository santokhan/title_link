<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
/**
 * This file required json data from client
 */
require_once(__DIR__ . '/../scrape_google/simplehtmldom_1_9_1/simple_html_dom.php');


$GLOBALS['title_link'] = [
    'title' => [],
    'title2' => [],
    'link' => [],
    'link2' => [],
    'related' => []
];
$demodata = [
    'title' => [
        "World's BIGGEST CAR WASH - Washing, Waxing, Drying - YouTube",
        "Car Wash & Car Cleaning Services in Dhaka Bangladesh | Sheba.xyz",
        "Car Wash & Polish: Best Car Cleaning Service in Bangladesh",
        "Car wash - Wikipedia",
        "Welcome to the number 1 Car wash in Bangladesh.",
        "100+ Car Wash Pictures | Download Free Images on Unsplash",
        "More from Speed Car Wash - Facebook",
        " 153076 Car wash Images, Stock Photos & Vectors - Shutterstock",
        " Car Wash & Express Detail - Mister Car Wash",
        " Car Wash - Walmart.com",
    ],
    'link' => [
        "https://www.youtube.com/watch%3Fv%3DPU5orW-mtVs&sa=U&ved=2ahUKEwiPu_HSncj5AhUzs5UCHUjkDDsQtwJ6BAgOEAE&usg=AOvVaw2aw94lRZYo5BOB2dv26qeU",
        "https://www.sheba.xyz/car-cleaning&sa=U&ved=2ahUKEwiPu_HSncj5AhUzs5UCHUjkDDsQFnoECAMQAg&usg=AOvVaw3TbzobToXPCX21ECV4zf69",
        "https://www.sheba.xyz/car-wash-polish&sa=U&ved=2ahUKEwiPu_HSncj5AhUzs5UCHUjkDDsQFnoECAkQAg&usg=AOvVaw2cz7AyymPI-aYLGMkKCsHQ",
        "https://en.wikipedia.org/wiki/Car_wash&sa=U&ved=2ahUKEwiPu_HSncj5AhUzs5UCHUjkDDsQFnoECAoQAg&usg=AOvVaw2o5kresG9qc_yl21Fnnuvf",
        "https://www.nasproauto.com/&sa=U&ved=2ahUKEwiPu_HSncj5AhUzs5UCHUjkDDsQFnoECAgQAg&usg=AOvVaw2ANl7BVpFjl_OCE-r94LNk",
        "https://unsplash.com/s/photos/car-wash&sa=U&ved=2ahUKEwiPu_HSncj5AhUzs5UCHUjkDDsQFnoECAQQAg&usg=AOvVaw2g_U3a-Suoj6kVfaTGsdt8",
        "https://www.facebook.com/SpeedCarWashDhaka/videos/best-international-car-washing-brand-speed-car-wash-now-in-bangladesh-pls-visit-/297070238285155/&sa=U&ved=2ahUKEwiPu_HSncj5AhUzs5UCHUjkDDsQtwJ6BAgNEAE&usg=AOvVaw2Q3f0aaHyjt2PbZMl5EXm4",
        " https://www.shutterstock.com/search/car-wash&sa=U&ved=2ahUKEwiPu_HSncj5AhUzs5UCHUjkDDsQFnoECAIQAg&usg=AOvVaw2OZZyllCRp7ctOITJa_TtZ",
        " https://mistercarwash.com/car-wash/&sa=U&ved=2ahUKEwiPu_HSncj5AhUzs5UCHUjkDDsQFnoECAwQAg&usg=AOvVaw2UvdtaGcDsQEXzIONZxO1T",
        " https://www.walmart.com/browse/auto-tires/car-wash/91083_1212910_1212912&sa=U&ved=2ahUKEwiPu_HSncj5AhUzs5UCHUjkDDsQFnoECAEQAg&usg=AOvVaw2FQvozkd6iYxmAReO-Jizy",
    ],
    'related' => []
];
$redirect = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $get_input_from_client = file_get_contents('php://input');
    $input = json_decode($get_input_from_client, true);
    if (str_contains($input['search'], ' ')) {
        $input = str_replace(" ", "+", $input['search']);
    }
    $keyword = json_encode($input);
    scrape_google($keyword);
}


function scrape_google($keyword): void
{
    $get_html_dom = file_get_html("https://www.google.com/search?q=allintitle:$keyword");
    if (gettype($get_html_dom) === 'object') {
        /**
         * search result = #appbar
         * 
         * id main = #main #rcnt #center_col #res
         *     
         * id search keyword = #main #rcnt #center_col #res #bres a[data-xbu]
         * 
         * document.querySelectorAll("#rso > div > div > div > div > div a")
         * 
         * '#rso a[href^=https][data-ved][ping]'
         */
        foreach ($get_html_dom->find('#main a[href]') as $a) {
            $url = $a->getAttribute('href');
            if (domain_cheker($url)) {
                foreach ($a->find('h3') as $title) {
                    if (isset($title)) {
                        $GLOBALS['title_link']['title'][] = $title->plaintext;

                        $trim_url = trim($url, '/url?q=');
                        $GLOBALS['title_link']['link'][] = $trim_url;
                    }
                }
            } else {
                global $redirect;
                $redirect = true;

                if (str_contains($url, 'https://')) {
                    foreach ($a->find('h3') as $title) {
                        if (isset($title)) {
                            $GLOBALS['title_link']['title2'][] = $title->plaintext;

                            $trim_url = trim($url, '/url?q=');
                            $GLOBALS['title_link']['link2'][] = $trim_url;
                        }
                    }
                }
            }
        }

        /**
         * Response back to client
         */
        echo json_encode($GLOBALS['title_link']);

        // related-question-pair
        // foreach ($get_html_dom->find(".related-question-pair") as $keyword) {
        //     echo $keyword->plaintext . '<br/>';
        // $GLOBALS['title_link']['related'][] = $keyword->plaintext;
        // }

        // var_dump($get_html_dom->find("[id=result-stats]"));
        // var_dump($get_html_dom->find("[data-xbu]"));

        //'#bres a[data-xbu]'
        // foreach ($get_html_dom->find("a[data-xbu]") as $keyword) {
        //     echo $keyword->plaintext . '<br/>';

        //     $GLOBALS['title_link']['related'][] = $keyword->plaintext;
        // }
    }
}


function scrape_paid(): string
{
    $ch = curl_init('https://api.keywordseverywhere.com/v1/get_keyword_data');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: Bearer 58dc40a5413c50ed6bcb'
    ));

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt(
        $ch,
        CURLOPT_POSTFIELDS,
        urldecode(http_build_query([
            "dataSource" => "gkp",
            "country" => "us",
            "currency" => "USD",
            "kw" => [
                "keywords tool",
                "keyword planner",
            ]
        ]))
    );

    $data = curl_exec($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($info['http_code'] == 200) {
        return $data;
    } else {
        return  $data;
    }
}


// validate
function domain_cheker(string $url): bool
{
    $status = false;
    if (str_contains($url, 'https://')) {
        $valid_domain = ["slideshare.net", "pinterest.com", "reddit.com", "tumblr.com", "vk.com", "medium.com", "linkedin.com", "quora.com", "groups.google.com", "wix.com", "answers.com", "scribd.com", "acebook.com", "twitter.com", "instagram.com", "books.google.com", "researchgate.net", "scholar.google.com",];
        foreach ($valid_domain as  $domain) {
            $status = str_contains($url, $domain) ? true : false;
            if ($status) {
                return $status;
            }
        };


        $host_filter = [".blogspot", ".wordpress", ".weebly", "/forum", "/index", "/topic", "/forums", "community", "thread", "threads", "communities", "/comments"];
        foreach ($host_filter as  $host) {
            $status = str_contains($url, $host) ? true : false;
            if ($status) {
                return $status;
            }
        };
    }
    return $status;
}
// var_dump(domain_cheker('https://www.pinterest.om/forum'));


function print_array($var): void
{
    echo '<pre>';
    if (gettype($var) === 'array') {
        print_r($var);
    } else {
        echo $var;
    }
    echo '</pre>';
}
