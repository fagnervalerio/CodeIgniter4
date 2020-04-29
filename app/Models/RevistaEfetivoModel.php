<?php namespace App\Models;

use CodeIgniter\Model;

class RevistaEfetivoModel extends Model
{
    protected $table         = 'revista_efetivo';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $DBGroup       = 'mysql';
    protected $allowedFields = [
        'revista_id', 'efetivo_id', 'afastamento_id', 'efetivo_responsavel_id', 'datahora'
    ];
}