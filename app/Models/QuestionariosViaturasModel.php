<?php namespace App\Models;

use CodeIgniter\Model;

class QuestionariosViaturasModel extends Model
{
    protected $table         = 'ps_questionarios_viaturas';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
}