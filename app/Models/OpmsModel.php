<?php namespace App\Models;

use CodeIgniter\Model;

class OpmsModel extends Model
{
    protected $table         = 'opms';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $DBGroup = 'mysql';
}