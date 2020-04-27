<?php namespace App\Models;

use CodeIgniter\Model;

class AfastamentosModel extends Model
{
    protected $table         = 'afastamentos';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $DBGroup = 'mysql';
}