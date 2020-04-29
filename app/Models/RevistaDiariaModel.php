<?php namespace App\Models;

use CodeIgniter\Model;

class RevistaDiariaModel extends Model
{
    protected $table         = 'revista_diaria';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $DBGroup       = 'mysql';
    protected $allowedFields = [
        'data_revista', 'hora_revista', 'efetivo_conferente_id', 'efetivo_assinante_id', 'situacao', 'usuario_responsavel_id', 'data_abertura'
    ];
}