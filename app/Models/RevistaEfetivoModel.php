<?php namespace App\Models;

use CodeIgniter\Model;

class RevistaEfetivoModel extends Model
{
    protected $table         = 'revista_efetivo';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $DBGroup       = 'mysql';
}