<?php namespace App\Models;

use CodeIgniter\Model;

class ViaturasModel extends Model
{
    protected $table         = 'ps_viaturas';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
}