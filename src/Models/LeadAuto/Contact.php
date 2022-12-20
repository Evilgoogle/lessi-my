<?php
namespace App\Models\LeadAuto;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    public $timestamps  = false;
    protected $table    = 'lead_auto_contacts';
    protected $fillable = [ 'id', 'lead_id', 'type', 'value'];
}
