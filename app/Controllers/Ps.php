<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\UnidadesModel;
use App\Models\ViaturasModel;
use App\Models\QuestionariosPerguntasModel;
use App\Models\QuestionariosViaturasModel;
use App\Models\QuestionariosModel;
use App\Models\PerguntasModel;
use App\Models\TipoPerguntasModel;
use App\Models\PerguntasRespostasModel;
use App\Models\RespostasModel;
use App\Models\QuestionariosPerguntasRespostasModel;

class Ps extends BaseController
{
    use ResponseTrait;

    private function cors()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Headers', 'X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
    }

	public function index()
	{
		return "OK";
	}

    public function unidades()
    {
        $unidadesModel = new UnidadesModel();
        $unidades = $unidadesModel->findAll();

        $this->cors();
        return $this->respond($unidades, 200);
    }

    public function viaturas($unidade = null)
    {
        $vtrModel = new ViaturasModel();
        $questionariosViaturasModel = new QuestionariosViaturasModel();
        $questionariosModel = new QuestionariosModel();

        if($unidade) {
            // Busca as vtr da unidade
            $viaturas = $vtrModel->where("unidade = '$unidade'")->findAll();
        }
        else {
            // Retorna todas as vtr            
            $viaturas = $vtrModel->findAll();
        }

        if($viaturas) {
            foreach($viaturas as $viatura) {
               $viatura->ps_questionarios_viaturas = $questionariosViaturasModel->where("viatura_id", $viatura->id)->findAll();
               if($viatura->ps_questionarios_viaturas) {
                   foreach($viatura->ps_questionarios_viaturas as $questionarioViatura) {
                       $questionarioViatura->ps_questionarios = $questionariosModel->find($questionarioViatura->questionario_id);
                   }
               }
            }  
        }

        $this->cors();
        return $this->respond($viaturas, 200);
    }

    public function questoes($questionario_id)
    {
        $questionariosPerguntasModel = new QuestionariosPerguntasModel();
        $perguntasModel = new PerguntasModel();
        $tipoPerguntasModel = new TipoPerguntasModel();
        $perguntasRespostasModel = new PerguntasRespostasModel();
        $respostasModel = new RespostasModel();
        $questoes = $questionariosPerguntasModel->where("questionario_id", $questionario_id)->orderBy("ordem", "asc")->findAll();
        foreach($questoes as $questao) {
            // Pega os dados da pergunta
            $questao->ps_pergunta = $perguntasModel->find($questao->pergunta_id);
            if($questao->ps_pergunta) {
                $questao->ps_pergunta->ps_tipo_pergunta = $tipoPerguntasModel->find($questao->ps_pergunta->tipo_pergunta_id);
                $questao->ps_pergunta->ps_perguntas_respostas = $perguntasRespostasModel->where("pergunta_id", $questao->ps_pergunta->id)->orderBy("ordem", "desc")->findAll();
                foreach($questao->ps_pergunta->ps_perguntas_respostas as $perguntaResposta) {
                    $perguntaResposta->ps_resposta = $respostasModel->find($perguntaResposta->resposta_id);
                }
            }
        }
        
        $this->cors();
        return $this->respond($questoes, 200);
    }

    public function respondidas($email, $unidade, $funcao)
    {
        $db = \Config\Database::connect();
        $sql = "select distinct c.*, date(a.data_resposta) data_resposta, a.email, a.unidade opm, a.funcao
		        from ps_questionarios_perguntas_respostas a
		        join ps_viaturas c on a.viatura_id = c.id
		        where a.email = '$email' and a.unidade = '$unidade' and a.funcao = '$funcao'";
        $result = $db->query($sql)->getResult();
        
        $this->cors();
        return $this->respond($result, 200);
    }

    public function responder()
    {
        $data = $this->request->getJSON();
        if($data->respostas) {
            foreach($data->respostas as $pergunta => $resposta) {
                $qprModel = new QuestionariosPerguntasRespostasModel();
                $qpr = [
                    "questionario_id" => $data->questionario_id,
                    "viatura_id" => $data->viatura_id,
                    "pergunta_id" => $pergunta,
                    "funcao" => $data->funcao,
                    "unidade" => $data->unidade,
                    "email" => $data->email,
                    "data_resposta" => date("Y-m-d H:i:s"),
                    "resposta_id" => ($data->respostas->{$pergunta}->tipo != "TEXTO") ? $data->respostas->{$pergunta}->valor : 0,
                    "resposta_texto" => ($data->respostas->{$pergunta}->tipo == "TEXTO") ? $data->respostas->{$pergunta}->valor : ''
                ];
                $qprModel->save($qpr);
            }
        } 
                
        $this->cors();
        return $this->respond($data, 200);
    }
}