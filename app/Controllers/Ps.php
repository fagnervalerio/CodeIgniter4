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
use App\Models\PesquisaModel;

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
    
    public function pesquisa()
    {
        $pesquisaModel = new PesquisaModel();
        $pesquisa = $pesquisaModel->where("datetime() BETWEEN data_inicio AND data_termino")->findAll();

        $this->cors();
        return $this->respond($pesquisa, 200);
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
                $viatura->icone = "vtr_bomb_" . $viatura->tipo_operacional . ".png";
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
                    "observacao" => (isset($data->obs)) ? $data->obs : "",
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

    public function resultado()
    {
        helper("csmmopb");

        $db = \Config\Database::connect();
        $sql = "select distinct c.*, date(a.data_resposta) data_resposta, a.email, a.unidade opm, a.funcao
		        from ps_questionarios_perguntas_respostas a
		        join ps_viaturas c on a.viatura_id = c.id";
        $result = $db->query($sql)->getResult();

        // Divide por unidade
        foreach($result as $item) {
            $data["unidades"][$item->opm][] = $item;
        }

        // Divide por viatura
        foreach($result as $item) {
            $data["viaturas"][$item->prefixo][] = $item;
        }

        // Divide por funcao
        foreach($result as $item) {
            $data["funcao"][strtolower($item->funcao)][] = $item;
        }

        $sql = "select distinct a.unidade opm, a.funcao, a.questionario_id, a.viatura_id
		        from ps_questionarios_perguntas_respostas a";
        $data["preechidas"] = $db->query($sql)->getResult();

        $sql = "select c.*, a.*, q.titulo questionario, p.titulo pergunta, p.descricao, tp.descricao tipo_pergunta, p.tipo_pergunta_id, r.descricao resposta
		        from ps_questionarios_perguntas_respostas a
                join ps_viaturas c on a.viatura_id = c.id
                join ps_questionarios q on a.questionario_id = q.id
                join ps_perguntas p on a.pergunta_id = p.id
                join ps_tipo_perguntas tp on p.tipo_pergunta_id = tp.id
                left join ps_respostas r on a.resposta_id = r.id
                order by tp.codigo asc";
        $result = $db->query($sql)->getResult();

        $respostasModel = new RespostasModel();
        $respostas = $respostasModel->findAll();
        $resposta = [
            "titulo" => "",
            "quesito" => "",
            "respondentes" => [],
            "respostas" => []
        ];
        foreach($respostas as $item) {
            $resposta["respostas"][$item->descricao] = 0;
        }        

        // Totaliza por questionario e por questão
        foreach($result as $item) {
            if($item->resposta_id > 0) {
                // Cria o hash unico
                $hash = md5($item->funcao . $item->email . $item->unidade);

                if(isset($data["questionarios"][$item->questionario][$item->tipo_pergunta])) {
                    $data["questionarios"][$item->questionario][$item->tipo_pergunta]["respondentes"][$hash] = null;
                    $data["questionarios"][$item->questionario][$item->tipo_pergunta]["respostas"][$item->resposta] += $item->resposta_id;
                }
                else {
                    $r = $resposta;
                    $r["identificacao"] = $item->questionario_id . "_" . $item->tipo_pergunta_id;                    
                    $r["respondentes"][$hash] = null;
                    $r["respostas"][$item->resposta] += $item->resposta_id;
                    $data["questionarios"][$item->questionario][$item->tipo_pergunta] = $r;
                }
            }
        }

        /********* */
        
        $ano_atual = date("Y");
        $result = [];

        $ci = 0;
        $respostasModel = new RespostasModel();
        $respostasArray = $respostasModel->findAll();
        $respostas = [];
        foreach($respostasArray as $resposta) {
            $resposta->ci = $ci + 1;
            $resposta->cf = $resposta->ci + 19;
            $ci = $resposta->cf;
            $resposta->total = 0;
            $respostas[$resposta->id] = $resposta;
        }

        $data["respostas"] = $respostas;

        // echo "<pre>";
        // print_r($respostas);

        // Carrega as Unidades
        $unidadesModel = new UnidadesModel();
        $unidades = $unidadesModel->findAll();
        foreach($unidades as $unidade) {            
            // Carrega as Vtrs da Unidade
            $viaturasModel = new ViaturasModel();
            $viaturas = $viaturasModel->where("unidade = '$unidade->unidade'")->findAll();
            foreach($viaturas as $viatura) {
                foreach($respostasArray as $resposta) {
                    $viatura->respostas[$resposta->id] = 0;
                }

                // Verifica se a VTR teve questionario no ano corrente
                $sql = "select q.*
                        from ps_questionarios_viaturas qv
                        join ps_questionarios q on qv.questionario_id = q.id
                        where qv.viatura_id = $viatura->id
                        and q.ano_base = $ano_atual";
                $questionario = $db->query($sql)->getRow();
                if($questionario) {
                    // Calcula o conceito total do questionário preenchido
                    $sql = "select p.*, qpr.resposta_id
                            from ps_perguntas p
                            join ps_questionarios_perguntas qp on qp.pergunta_id = p.id
                            join ps_questionarios_perguntas_respostas qpr on qpr.pergunta_id = p.id and qpr.questionario_id = qp.questionario_id
                            where qp.questionario_id = $questionario->id
                            and qpr.viatura_id = $viatura->id
                            and qpr.resposta_id > 0
                            order by qp.ordem asc";
                    $perguntas = $db->query($sql)->getResult();                    
                    if($perguntas) {
                        $conceito_total = count($perguntas) * 5;
                        $conceito_atual = 0;                        
                        foreach($perguntas as $pergunta) {
                            // Soma o valor das respostas das perguntas
                            $resp = ($pergunta->resposta_id) ? $pergunta->resposta_id : 5;
                            $conceito_atual += $resp;
                            $viatura->respostas[$resp]++;
                        }
                        $conceito = intval(($conceito_atual / $conceito_total) * 100);
                        foreach($respostas as $resposta) {
                            if($conceito >= $resposta->ci && $conceito <= $resposta->cf) {
                                $conceito_vtr = $resposta->descricao;
                            }                        
                        }
                    }
                    else {
                        $conceito_total = 0;
                        $conceito_atual = 0;
                        $conceito = 0;
                        $conceito_vtr = "S/ AVAL.";
                    }

                    $viatura->conceito_total = $conceito_total;
                    $viatura->conceito_atual = $conceito_atual;
                    $viatura->conceito_valor = $conceito;
                    $viatura->conceito = $conceito_vtr;                    
                }
            }

            // Adiciona os valores ao array de resposta
            $result[$unidade->id] = (object)[
                "unidade" => $unidade,
                "viaturas" => $viaturas
            ];
        }

        $data["dados"] = $result;

        // print_r($data);
        // echo "</pre>";
        
        $this->cors();
        return view('resultado', $data);
    }
}