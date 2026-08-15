<?php
namespace App\Models;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
class PurchaseRequest extends Model { use BelongsToCompany; protected $fillable=['company_id','number','title','status','reason','requested_by_id']; public function lines(){return $this->hasMany(PurchaseRequestLine::class);} }
