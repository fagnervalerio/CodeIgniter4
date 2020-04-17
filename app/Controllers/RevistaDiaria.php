<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\EfetivoModel;
use App\Models\OpmsModel;
use App\Models\PostoGraduacaoModel;

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
            [0, $opm->descricao, null]
        ];
        $result = array_merge($result, $this->opmSubEfet($opm_id));
        
        return $this->respond($result, 200);
    }

    private function opmSubEfet($opm_id)
    {
        $result = [];

        $opmModel = new OpmsModel();
        $opms = $opmModel->where("opm_pai_id = $opm_id")->findAll();
        if($opms) {
            foreach($opms as $opm) {
                // Opms Subordinadas
                $result[] = [1, $opm->descricao, null];

                // Efetivo
                $efetivoModel = new EfetivoModel();
                $efetivo = $efetivoModel->where("opm_id = '$opm->id' AND ativo = 1")->findAll();
                if($efetivo) {
                    foreach($efetivo as $pm) {
                        $pmData = $this->getUsuario($pm->id);
                        $result[] = [2, null, strtoupper("{$pmData->posto_graduacao->abreviacao} PM $pmData->nome_guerra")];
                    }
                }
            }
        }

        return $result;
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
