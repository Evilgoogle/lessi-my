<?php
namespace App\Controllers\Leasback\Status;

use App\Models\LeasBack\Auftrag as AttachAuftrag;
use App\Models\LeasBack\Fahrzeug as LeasFahrzeug;
use App\Models\MACS\Fahrzeughistorie;
use App\Models\LeasBack\StepStatus;
use App\Models\LeasBack\StepStatusChild;

/**
 * Description of Einladung
 *
 * @author EvilGoogle
 */
class Einladung 
{
    public $leasback;
    public $type;

    public function __construct($leasback_id, $type) 
    {
        $this->leasback = \App\Models\LeasBack\LeasBack::find($leasback_id);
        $this->type = $type;
    }
    
    private function number_normalize($carNumber) 
    {
	$str1 = preg_replace("/[0-9- ]/ui", "", $carNumber);
        $str2 = strtoupper(preg_replace("/[a-zA-Z- ]/ui", "", $carNumber));
        $v1_number = substr($str1,0,2) . "-" . substr($str1,2) . " " . $str2;
        $v1_number = ($str2 == '') ? $carNumber : $v1_number;
        $v1_number = strtoupper($v1_number);
	
	return $v1_number;
    }
    
    public function start()
    {
        $result = false;
        
        $block_1 = $this->main();
        $block_2 = $this->mail();
        
        if($block_1) {
            if($block_2) {
                $result = true;
            }
        }
        
        $step = StepStatus::where('leasback_id', $this->leasback->id)->where('step', 'einladung')->first();
        if($this->type == 'open') {
            if($result) {
                $step->status = 'success';
                $spte->success_time = date('Y-m-d h:i:s');
            }
        } elseif($this->type == 'close') {
            $step->status = 'notcompleted';
        }
        $step->save();
    }
    
    private function main()
    {
        $result = false;
        
        $fahrzeug = LeasFahrzeug::where('leasback_id', $this->leasback->id)->first();
        if(!isset($fahrzeug)) {
            $fahrzeug = Fahrzeughistorie::getCarInfoByNumber($this->number_normalize($this->leasback->mark));
        }
        
        $auftrag = AttachAuftrag::where('leasback_id', $this->leasback->id)->first();
  
        // ----
        if(isset($fahrzeug)) {
            if(isset($auftrag)) {
                $result = true;
            }
        }
        
        return $result;
    }
    
    private function mail()
    {
        $step = StepStatus::where('leasback_id', $this->leasback->id)->where('step', 'einladung')->first();
        
        $child = StepStatusChild::where('step_id', $step->id)->where('step', 'mail')->first();
        
        if($child->status == 'success') {
            return true;
        } else {
            return false;
        }
    }
}
