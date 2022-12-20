<?php
namespace App\Models\LeadAuto;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of EastwoodFahrzeug
 *
 * @author Evilgoogle
 */
class EastwoodFahrzeug extends Model
{
    protected $connection = 'mobile';
    public $timestamps	 = false;
    protected $table	 = 'fahrzeug';
}
