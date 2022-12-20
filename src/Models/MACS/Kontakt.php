<?php
namespace App\Models\MACS;

use Firebird\Eloquent\Model;

/**
 * Description of Kontakt
 *
 * @author joker
 */
class Kontakt extends Model 
{
    protected $connection = 'macs';
    protected $table      = 'KONTAKT';
    protected $primaryKey = 'KONTAKTID';
    public $timestamps    = false;
}
