<?php namespace App\Models;

use CodeIgniter\Model;

class PostoGraduacaoModel extends Model
{
    protected $table         = 'postos_graduacoes';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $DBGroup = 'mysql';
}