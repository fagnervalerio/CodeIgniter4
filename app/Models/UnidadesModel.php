<?php namespace App\Models;

use CodeIgniter\Model;

class UnidadesModel extends Model
{
    protected $table         = 'ps_unidades';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
}