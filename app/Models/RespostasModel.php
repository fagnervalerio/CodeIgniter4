<?php namespace App\Models;

use CodeIgniter\Model;

class RespostasModel extends Model
{
    protected $table         = 'ps_respostas';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
}