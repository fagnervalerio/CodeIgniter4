<?php namespace App\Controllers;

class Home extends BaseController
{
	public function index()
	{
		return view('welcome_message');
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
