<!doctype html>
<html lang="pt-BR">
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<title>Resultado da Pesquisa de Satisfação 2020</title>

		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
		<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
	</head>
	<body>
		<div class="container">
			<nav class="navbar navbar-light bg-light">				
				<span class="navbar-brand">
					<img src="<?= base_url("assets/images/Brasao-CB.svg") ?>" alt="" class="d-inline-block align-top" width="40">
					CSM/MOpB
					<small>Pesquisa de Satisfação</small>
				</span>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    				<span class="navbar-toggler-icon"></span>
  				</button>
  				<div class="collapse navbar-collapse" id="navbarNav">
    				<ul class="navbar-nav">
      					<li class="nav-item active">
        					<a class="nav-link" href="#">Geral <span class="sr-only">(current)</span></a>
      					</li>						
    				</ul>
  				</div>
			</nav>
			<br/>
			<div class="row">
				<div class="col">
					<div class="card">
						<h5 class="card-header">Número de Respostas</h5>
						<div class="card-body">
							<h5 class="card-title"><?= count($preechidas) ?></h5>
						</div>
					</div>
				</div>
				<div class="col">
					<div class="card">
						<h5 class="card-header">Unidade</h5>
						<div class="card-body">
							<h5 class="card-title"><?= count($unidades) ?></h5>
						</div>
					</div>
				</div>
				<div class="col">
					<div class="card">
						<h5 class="card-header">Viaturas</h5>
						<div class="card-body">
							<h5 class="card-title"><?= count($viaturas) ?></h5>
						</div>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<?php foreach($respostas as $item): ?>
					<div class="col">
						<h5 class="<?= alert_conceito($item->descricao) ?>"><?= $item->descricao ?></h5>							
					</div>
				<?php endforeach; ?>
			</div>
			<?php foreach($dados as $item): ?>
				<div class="row">
					<div class="col">
						<div class="card">
							<h5 class="card-header"><?= $item->unidade->unidade ?></h5>
							<div class="card-body">
								<h5 class="card-title">Viaturas pesquisadas</h5>
								<div class="row">
									<?php foreach($item->viaturas as $viatura): ?>									
										<div class="col-3">
											<div class="card mb-3">
												<h5 class="card-header"><?= $viatura->prefixo ?></h5>
												<div class="card-body">
													Conceito
													<h5 class="card-title <?= alert_conceito($viatura->conceito) ?>"><?= $viatura->conceito ?></h5>
													<a href="#" class="h5 mr-3" title="Gráfico Conceitual"><i class="fa fa-bar-chart"></i></a>
													<a href="#" class="h5 pull-right" title="Resultado detalhado"><i class="fa fa-search"></i></a>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<br/>
			<?php endforeach; ?>

			<?php foreach($questionarios as $questionario => $quesitos): ?>
				<div class="guide">
					<h4 style="background-color: #e2e2e2; padding: 10px;"><?= $questionario ?></h4>
					<?php foreach($quesitos as $quesito => $item): ?>
						<table width="100%" cellspacing="0" cellpadding="0">
							<tr>
								<td valign="top" style="border-bottom: 1px solid #e2e2e2; padding-top: 15px;">
									<?= $quesito ?>
									<br>
									<small>Total de Respostas: <?= count($item["respondentes"]) ?></small>
									<input type="hidden" id="labels_<?= $item["identificacao"] ?>" value='<?= json_encode(array_keys($item["respostas"])) ?>' />
									<input type="hidden" id="values_<?= $item["identificacao"] ?>" value='<?= json_encode(array_values($item["respostas"])) ?>' />
									<?php asort($item["respostas"]); $conceito = array_keys($item["respostas"]); ?>	
									<h2><span style="font-size: 0.5em; font-weight: normal; display: block">CONCEITO</span><?= end($conceito) ?></h2>									
								</td>
								<td width="45%" style="border-bottom: 1px solid #e2e2e2; padding-top: 15px;">
									<canvas id="<?= $item["identificacao"] ?>" class="grafico" width="300" height="150"></canvas>
								</td>
							</tr>
						</table>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

			<div class="footer">
				Page rendered in {elapsed_time} seconds. Environment: <?= ENVIRONMENT ?>
			</div>

		</div>

		<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
		
		<script>
			$(document).ready(() => {
				$(".grafico").each((idx, canvas) => {
					let idf = $(canvas).attr('id');
					let labels = $("#labels_" + idf).val();
					let values = $("#values_" + idf).val();
					var ctx = document.getElementById(idf).getContext('2d');
					var myChart = new Chart(ctx, {
						type: 'bar',
						data: {
							labels: JSON.parse(labels),
							datasets: [{
								label: 'Conceitos',
								data: JSON.parse(values),
								backgroundColor: [
									'rgba(255, 99, 132, 0.2)',
									'rgba(255, 159, 64, 0.2)',									
									'rgba(255, 206, 86, 0.2)',
									'rgba(54, 162, 235, 0.2)',
									'rgba(75, 192, 192, 0.2)',
									
								],
								borderColor: [
									'rgba(255, 99, 132, 1)',
									'rgba(255, 159, 64, 1)',									
									'rgba(255, 206, 86, 1)',
									'rgba(54, 162, 235, 1)',
									'rgba(75, 192, 192, 1)',
									
								],
								borderWidth: 1
							}]
						},
						options: {
							scales: {
								yAxes: [{
									ticks: {
										beginAtZero: true
									}
								}]
							}
						}
					});
				});
			});
		</script>

	</body>
</html>
