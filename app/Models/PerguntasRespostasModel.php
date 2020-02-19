<?php namespace App\Models;

use CodeIgniter\Model;

class PerguntasRespostasModel extends Model
{
    protected $table         = 'ps_perguntas_respostas';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
}