<?php namespace App\Models;

use CodeIgniter\Model;

class EfetivoModel extends Model
{
    protected $table         = 'efetivo';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $DBGroup = 'mysql';
}