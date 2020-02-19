<?php namespace App\Models;

use CodeIgniter\Model;

class TipoPerguntasModel extends Model
{
    protected $table         = 'ps_tipo_perguntas';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
}