<?php namespace App\Models;

use CodeIgniter\Model;

class QuestionariosPerguntasRespostasModel extends Model
{
    protected $table         = 'ps_questionarios_perguntas_respostas';
    protected $primaryKey = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = [
        "questionario_id",
        "viatura_id",
        "pergunta_id",
        "funcao",
        "unidade",
        "email",
        "data_resposta",
        "resposta_id",
        "resposta_texto",
        "observacao"
    ];
}