<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\EfetivoModel;
use App\Models\OpmsModel;
use App\Models\PostoGraduacaoModel;
use App\Models\RevistaDiariaModel;
use App\Models\RevistaEfetivoModel;
use App\Models\AfastamentosModel;

class RevistaDiaria extends BaseController
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
		$this->cors();
        return $this->respond(["CSM/MOpB - Corpo de Bombeiros de São Paulo"], 200);
    }

    public function identificar()
    {        
        $this->cors();

        $data = (object)$this->request->getJSON();
        $user = $data->re;
        $pass = $data->senha;

        // Verifica o usuario
        $efetivoModel = new EfetivoModel();
        $usuario = $efetivoModel->where("re = '$user' AND ativo = 1")->first();
        if($usuario) {
            // Verifica a Senha
            if($usuario->senha == md5($pass)) {                
                $pgModel = new PostoGraduacaoModel();
                $opmModel = new OpmsModel();

                $usuario->posto_graduacao = $pgModel->find($usuario->posto_graduacao_id);
                $usuario->opm = $opmModel->find($usuario->opm_id);
            }
            else {
                $usuario = ["erro" => "Senha inválida"];
            }
        }
        else {
            $usuario = ["erro" => "RE não encontrado"];
        }

        return $this->respond($usuario, 200);
    }

    private function getUsuario($user_id, $only_active = true)
    {
        $active = ($only_active) ? "ativo = 1" : "ativo >= 0";

        // Verifica o usuario
        $efetivoModel = new EfetivoModel();
        $usuario = $efetivoModel->where("id = '$user_id' AND $active")->first();
        if($usuario) {
            // Chaves estrangeiras            
            $pgModel = new PostoGraduacaoModel();
            $opmModel = new OpmsModel();

            $usuario->posto_graduacao = $pgModel->find($usuario->posto_graduacao_id);
            $usuario->opm = $opmModel->find($usuario->opm_id);            
        }

        return $usuario;        
    }

    public function listarOpms($opm_pai_id = null)
    {        
        $this->cors();

        $opmModel = new OpmsModel();
        if($opm_pai_id == null){
            $opms = $opmModel->findAll();
        }
        else {
            $opms = $opmModel->where("opm_pai_id = $opm_pai_id")->findAll();
        }

        return $this->respond($opms, 200);
    }

    public function listarEfetivo($opm_id)
    {        
        $this->cors();

        $opmModel = new OpmsModel();
        $opm = $opmModel->find($opm_id);

        $result = [
            ["nivel" => 0, "titulo" => $opm->descricao, "nome" => null]
        ];
        $result = array_merge($result, $this->opmSubEfet($opm_id));
        
        return $this->respond($result, 200);
    }

    private function opmSubEfet($opm_id)
    {
        $result = [];
        $revista = json_decode($this->revista()->getBody());

        $opmModel = new OpmsModel();
        $opms = $opmModel->where("opm_pai_id = $opm_id")->findAll();
        if($opms) {
            foreach($opms as $opm) {
                // Opms Subordinadas
                $result[] = ["nivel" => 1, "titulo" => $opm->descricao, "nome" => null];

                // Efetivo
                $efetivoModel = new EfetivoModel();
                $efetivo = $efetivoModel->where("opm_id = '$opm->id' AND ativo = 1")->findAll();
                if($efetivo) {
                    foreach($efetivo as $pm) {
                        $pmData = $this->getUsuario($pm->id);
                        $efet = ["id" => $pm->id, "nivel" => 2, "titulo" => null, "nome" => strtoupper("{$pmData->posto_graduacao->abreviacao} PM $pmData->nome_guerra")];
                        if(isset($revista->id)) {
                            $sts = json_decode($this->revistaUsuarioStatus($revista->id, $pm->id)->getBody());
                            if($sts) {
                                $efet["situacao"] = $sts->afastamento_id;
                                $efet["situacao_descricao"] = $sts->afastamento;
                            }                            
                        }
                        $result[] = $efet;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Situação da Revista
     * 0 - cancelada
     * 1 - Iniciada, em preenchimento
     * 2 - Iniciada, conferida pelo sgt de dia
     * 3 - Finalizada, conferida pelo oficial de dia
     */

    public function revista($revista_id = false)
    {        
        $this->cors();

        $revModel = new RevistaDiariaModel();
        
        if($revista_id) {
            // Retoirna os dados da revista solicitada
            $revista = $revModel->find($revista_id);
        }
        else {
            $revista = $revModel->where("situacao = 1")->first();
        }
        
        $result = (object)[
            "id" => "",
            "data_hora" => "",
            "sargento_dia" => "",
            "oficial_dia" => "",
            "em_forma" => "0",
            "novidades" => "0",
            "total" => "0"
        ];

        // Se achou uma revista, retorna os dados solicitados
        if($revista) {
            $result->id = $revista->id;
            $result->data_hora = $revista->data_revista . " " . $revista->hora_revista;

            // Pega o SGT
            $sgtDia = $this->getUsuario($revista->efetivo_conferente_id);
            $result->sargento_dia = strtoupper($sgtDia->posto_graduacao->abreviacao) . " PM " . $sgtDia->nome_guerra;

            // Pega o OF
            $ofDia = $this->getUsuario($revista->efetivo_assinante_id);
            $result->oficial_dia = strtoupper($ofDia->posto_graduacao->abreviacao) . " PM " . $ofDia->nome_guerra;

            return $this->respond($result, 200);
        }
        else {
            return $this->respond(["error" => "Não foi encontrada nenhuma revista"], 400);
        }
    }

    public function revistasAnteriores($dtIni, $dtFim)
    {        
        $this->cors();

        $revModel = new RevistaDiariaModel();        
        $revistas = $revModel->where("situacao = 3 AND data_revista BETWEEN '$dtIni' AND '$dtFim'")->findAll();
                
        $result = [];

        // Se achou uma revista, retorna os dados solicitados
        if($revistas) {
            foreach($revistas as $item) {
                $result[] = json_decode($this->revista($item->id)->getBody());
            }
            
            return $this->respond($result, 200);
        }
        else {
            return $this->respond(["error" => "Não foi encontrada nenhuma revista"], 400);
        }
    }

    public function revistaUsuarioStatus($revista_id, $usuario_id)
    {
        $this->cors();
        $result = [];

        $revEfet = new RevistaEfetivoModel();
        $rst = $revEfet->where("revista_id = $revista_id AND efetivo_id = $usuario_id")->first();
        if($rst) {
            $result = $rst;

            // Pega o afastamento
            $afast = json_decode($this->afastamento($result->afastamento_id)->getBody());
            $result->afastamento = $afast->descricao;
        }

        return $this->respond($result, 200);
    }

    public function listarSecoes()
    {        
        $this->cors();
        $result[] = [
            "secao" => "",
            "responsavel" => "",
            "em_forma" => "0",
            "novidades" => "0",
            "total" => "0",
            "id" => "0",
        ];

        $opmModel = new OpmsModel();
        $opms = $opmModel->where("opm_pai_id = 0")->findAll();
        if($opms) {
            $result = [];
            foreach($opms as $opm) {
                // Pega o resposável
                $resp = $this->getUsuario($opm->efetivo_responsavel_id);
                $result[] = [
                    "secao" => $opm->descricao,
                    "responsavel" => strtoupper($resp->posto_graduacao->abreviacao) . " PM " . $resp->nome_guerra,
                    "em_forma" => "0",
                    "novidades" => "0",
                    "total" => "0",
                    "id" => "$opm->id",
                ];
            }            
        }

        return $this->respond($result, 200);
    }

    public function listarAfastamentos()
    {        
        $this->cors();

        $afastModel = new AfastamentosModel();
        $afast = $afastModel->findAll();

        return $this->respond($afast, 200);
    }

    public function afastamento($afast_id)
    {        
        $this->cors();

        $afastModel = new AfastamentosModel();
        $afast = $afastModel->find($afast_id);

        return $this->respond($afast, 200);
    }

    public function novaRevista()
    {
        $this->cors();

        $postData = (object)$this->request->getJSON();
        
        $revDiaria = new RevistaDiariaModel();

        $revista = json_decode($this->revista()->getBody());
        if(!isset($revista->error)) {
            $result = false;
        }
        else {
            $data = [
                "data_revista" => $postData->data_revista,
                "hora_revista" => $postData->hora_revista,
                "usuario_abertura_id" => $postData->usuario_responsavel,
                "efetivo_conferente_id" => 7,
                "efetivo_assinante_id" => 20,
                "data_abertura" => date("Y-m-d H:i:s"),
                "situacao" => 1
            ];
            $revDiaria->save($data);
            $result = true;
        }

        return $this->respond($result, 200);
    }

    public function resultadoEfetivo()
    {
        $this->cors();

        $postData = (object)$this->request->getJSON();
        $data = [
            "revista_id" => $postData->revista_id,
            "efetivo_id" => $postData->efetivo_id,
            "afastamento_id" => $postData->afastamento_id,
            "datahora" => $postData->datahora,
            "efetivo_responsavel_id" => $postData->efetivo_responsavel_id
        ];

        $resultModel = new RevistaEfetivoModel();
        $rst = $resultModel->where("efetivo_id = $postData->efetivo_id AND revista_id = $postData->revista_id")->first();
        if($rst) {
            $data["id"] = $rst->id;
        }
            
        $result = $resultModel->save($data);
        
        return $this->respond($result, 200);
    }

	//--------------------------------------------------------------------

	public function send()
	{
		echo "Enviando e-mail...";

		$config['userAgent'] = 'CodeIgniter';
		$config['protocol'] = 'smtp';
		$config['SMTPHost'] = 'smtp.gmail.com';
        $config['SMTPPort'] = '587';
        $config['SMTPUser'] = 'csmmopbmgeral@gmail.com';
		$config['SMTPPass'] = 'nrgnmmobhppikmqc';
		$config['SMTPCrypto'] = 'tls';
		$config['SMTPKeepAlive'] = true;
		$config['priority'] = 1;
        $config['charset'] = 'utf-8';
		$config['newline'] = "\r\n";
		$config['CRLF'] = "\r\n";
		$config['mailType'] = 'html';

		$this->email->clear(true);
		$this->email->initialize($config);

		$this->email->setFrom('csmmopbmecanicageral@policiamilitar.sp.gov.br', 'CSM/MOpB Mecânica Geral');
		$this->email->setTo(['fagnervalerio@policiamilitar.sp.gov.br']);
		$this->email->setCc(['carloseduardoschiman@policiamilitar.sp.gov.br']);
		
		$this->email->setSubject('Pesquisa de Satisfação');
		$this->email->setMessage('Testing the email class.');

		if($this->email->send(false)) {
			echo "OK!";
		}
		else {
			echo "FAIL!<br>";
			echo "<pre>" . $this->email->printDebugger() . "</pre>";
		}
	}

}
