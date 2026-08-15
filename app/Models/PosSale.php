<?php
namespace App\Models;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
class PosSale extends Model { use BelongsToCompany; protected $fillable=['company_id','pos_session_id','receipt_number','total_amount','payment_method','status','created_by_id']; }
