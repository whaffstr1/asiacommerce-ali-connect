<pre>
<?php

$ch = curl_init("https://api2.asiacommerce.net/api/v2/country?filter[name][like]=Indonesia");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
'Content-Type: application/json')
);
$result = curl_exec($ch);
curl_close($ch);

$arr_res = json_decode($result,true);
$id_country = $arr_res['data'][0]['id'];

$ch = curl_init("https://api2.asiacommerce.net/api/v2/logistic-rates?page[num]=1&page[limit]=100&filter[origin_country_id][is]=99&filter[destination_country_id][is]=222&filter[origin_type]=Provinces&sort=-rate_per_weight&diverse=International&include=country,courier,destinationCountry");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
'Content-Type: application/json')
);
$result = curl_exec($ch);
curl_close($ch);

$berat_barang = 0.7;

$arr_res = json_decode($result,true);
$temp = array();
$selisih_min = 100;
if($arr_res['success']==1){
    foreach($arr_res['data'] as $number => $data){
        $selisih_now = abs($berat_barang-$data['attributes']['rate_per_weight']);
        if($selisih_now<$selisih_min){
            $selisih_min = $selisih_now;
            $harga_rate = $data['attributes']['rate'];
            $courier_id = $data['attributes']['courier_id'];
            $service_name = $data['attributes']['name'];
            $remarks = $data['attributes']['remarks'];
        }
        echo $number. "\t". $data['attributes']['rate_per_weight']. "\t". $selisih_now. "\t"."selisih minimum = $selisih_min"."\t"."rate now = $harga_rate"."\n";
        
    }
    echo $arr_res['included']['courier'][$courier_id]['attributes']['name']." ".$service_name." (".$remarks.") "." $harga_rate"."\n";
}

$array2d = array();
var_dump($array2d[199]['-']);
var_dump($array2d[9]['eubayou']);

$array2d[199]['-'] = 909;
$array2d[9]['eubayou'] = 1009;
$array2d[199]['yeme99'] = 809;

foreach($array2d as $kay => $val){
    foreach($val as $k => $val2){
        // echo $kay."  ".$k."\n";
        echo $array2d[$kay][$k]."\n";
    }
}

// if($array2d[199]['-']){
//     echo "ada.\n";
// }else{
//     echo "g ada.\n";
// }

//print_r($arr_res);


// https://api2.asiacommerce.net/api/v2/logistic-rates?page[num]=1&page[limit]=100&filter[origin_country_id][is]=99&filter[destination_country_id][is]=222&sort=-created_at&diverse=International&include=country,courier,destinationCountry

?>
</pre>