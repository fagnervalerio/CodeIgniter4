<?php namespace App\Models;

use CodeIgniter\Model;

class QuestionariosModel extends Model
{
    protected $table         = 'ps_questionarios';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
}