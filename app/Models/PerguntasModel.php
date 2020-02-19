<?php namespace App\Models;

use CodeIgniter\Model;

class PerguntasModel extends Model
{
    protected $table         = 'ps_perguntas';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
}