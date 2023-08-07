<?

/* modification begin */
$country_code = $_SERVER["HTTP_CF_IPCOUNTRY"];
$lang = pll_current_language();

if (($name == "pa_size") && ($lang == "ja" || $country_code == "JP")){

    $jp_size_chart_replacement = array(
        ">39-43" => ">24.0cm - 27.5cm",
        ">43-47" => ">28.0cm - 31.0cm",
        ">36" => ">22.0cm",
        ">37" => ">23.0cm",
        ">38" => ">24.0cm",
        ">39" => ">24.5cm",
        ">40" => ">25.5cm",
        ">41" => ">26.5cm",
        ">42" => ">27.0cm",
        ">43" => ">28.0cm",
        ">44" => ">28.5cm",
        ">45" => ">29.0cm",
        ">46" => ">30.0cm",
        ">47" => ">30.5cm",
        ">48" => ">31.5cm",
    );
    
    $html = strtr($html, $jp_size_chart_replacement);
}

if (($name == "pa_size") && ($country_code == "UK" || $country_code == "GB" || $country_code == "SG" || $country_code == "HK")){

    $uk_size_chart_replacement = array(
        ">39-43" => ">UK 5 - UK 8.5",
        ">43-47" => ">UK 9 - UK 12",
        ">36" => ">UK 3",
        ">37" => ">UK 4",
        ">38" => ">UK 5",
        ">39" => ">UK 5.5",
        ">40" => ">UK 6",
        ">41" => ">UK 7",
        ">42" => ">UK 7.5",
        ">43" => ">UK 8.5",
        ">44" => ">UK 9",
        ">45" => ">UK 10",
        ">46" => ">UK 11",
        ">47" => ">UK 11.5",
        ">48" => ">UK 12.5",
    );
    
    $html = strtr($html, $uk_size_chart_replacement);
}

if (($name == "pa_size") && ($country_code == "AU" || $country_code == "CA" || $country_code == "US")){
    
    if (stripos($html, ">36") !== FALSE){ //Women Size Chart
        $us_size_chart_replacement = array(
            ">39-43" => ">US 7 - US 10.5",
            ">43-47" => ">US 11 - US 14",
            ">36" => ">US 5",
            ">37" => ">US 6",
            ">38" => ">US 7",
            ">39" => ">US 7.5",
            ">40" => ">US 8",
            ">41" => ">US 9",
            ">42" => ">US 9.5",
            ">43" => ">US 10.5",
            ">44" => ">US 11",
            ">45" => ">US 12",
            ">46" => ">US 13",
            ">47" => ">US 13.5",
            ">48" => ">US 14.5",
        );
    } else {
        $us_size_chart_replacement = array(
            ">39-43" => ">US 6 - US 9.5",
            ">43-47" => ">US 10 - US 13",
            ">36" => ">US 4",
            ">37" => ">US 5",
            ">38" => ">US 6",
            ">39" => ">US 6.5",
            ">40" => ">US 7",
            ">41" => ">US 8",
            ">42" => ">US 8.5",
            ">43" => ">US 9.5",
            ">44" => ">US 10",
            ">45" => ">US 11",
            ">46" => ">US 12",
            ">47" => ">US 12.5",
            ">48" => ">US 13.5",
        );
    }
    
    $html = strtr($html, $us_size_chart_replacement);
}

/* modification end */

?>