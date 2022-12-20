<?php


namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class FzAngebotPos extends Model
{

    protected $connection = 'macs';
    protected $table      = 'FZANGEBOTPOS';
    protected $primaryKey = 'FZANGEBOTPOSID';
    public $timestamps    = false;

    

}
