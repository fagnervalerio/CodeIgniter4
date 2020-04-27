<?php namespace App\Models;

use CodeIgniter\Model;

class RevistaDiariaModel extends Model
{
    protected $table         = 'revista_diaria';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $DBGroup       = 'mysql';
}