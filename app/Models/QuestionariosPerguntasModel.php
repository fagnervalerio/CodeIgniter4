<?php namespace App\Models;

use CodeIgniter\Model;

class QuestionariosPerguntasModel extends Model
{
    protected $table         = 'ps_questionarios_perguntas';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
}