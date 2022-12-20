<?php
namespace App\Models\LeadAuto;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of Managers
 *
 * @author Evilgoogle
 */
class User extends Model
{
    protected $table = 'lead_auto_users';
    protected $primaryKey = 'user_id';
}
