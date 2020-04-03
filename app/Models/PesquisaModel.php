<?php namespace App\Models;

use CodeIgniter\Model;

class PesquisaModel extends Model
{
    protected $table         = 'ps_pesquisa';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
}