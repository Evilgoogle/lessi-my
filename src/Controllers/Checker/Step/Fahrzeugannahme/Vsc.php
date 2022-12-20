<?php
namespace App\Controllers\Checker\Step\Fahrzeugannahme;

use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Models\MACS;

class Vsc extends Controller
{   
    static function get($vin)
    {
        $data = json_encode([
            'vin' => $vin
        ]);
        
        $get = curl_init();
        curl_setopt($get, CURLOPT_URL, 'http://78.46.150.207/dsr');
        curl_setopt($get, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($get, CURLOPT_POST, true);
        curl_setopt($get, CURLOPT_POSTFIELDS, $data);
        curl_setopt($get, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: '.strlen($data),
        ]);
        $result = json_decode(curl_exec($get));
        curl_close($get);
        
        return $result;
    }
}
