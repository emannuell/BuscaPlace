<?php
session_start();
include "Conexao.class.php";
//cria os objetos
$conexao = new Conexao();
$funcao = new Funcao();
//conecta
$conexao->conectar();

if (!isset($_SESSION['email']) && !isset($_SESSION['id'])) {
	echo "<meta http-equiv='refresh' content='0, url=../login.php'>";
}else{
	$cliente_id = $_SESSION['id'];
	$email = $_SESSION['email'];
	$nome = $_SESSION['nome'];
	$sql = "SELECT * FROM REGISTRO WHERE CLIENTE_ID = '$cliente_id'";
	$arquivos = $conexao->execSQL($sql);
	?>
	<html>
	<head>
		<title>Painel de controle</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<style>
		html, body {
			height: 100%;
			margin: 0;
			padding: 0;
		}
		#map { height: 35%;
		}
		</style>
	</head>
	<body>
		<script>
		var map;
		var infoWindow;
		var service;
		var arr = [];
		var contador = 0;

		function initMap() {
			map = new google.maps.Map(document.getElementById('map'), {
				center: {lat: -25.4951519, lng: -49.2874025},
				zoom: 12,
				styles: [{
					stylers: [{ visibility: 'simplified' }]
				}, {
					elementType: 'labels',
					stylers: [{ visibility: 'off' }]
				}]
			});
			var geocoder = new google.maps.Geocoder();
			document.getElementById('submit').addEventListener('click', function() {
				geocodeAddress(geocoder, map);
			});
			infoWindow = new google.maps.InfoWindow();
			service = new google.maps.places.PlacesService(map);
			map.addListener('idle', performSearch);
		}

		function geocodeAddress(geocoder, resultsMap) {
			var address = document.getElementById('address').value;
			geocoder.geocode({'address': address}, function(results, status) {
				if (status === google.maps.GeocoderStatus.OK) {
					resultsMap.setCenter(results[0].geometry.location);
				} else {
					alert('Geocode was not successful for the following reason: ' + status);
				}
			});
		}
		function performSearch() {
			var request = {
				bounds: map.getBounds(),
				keyword: document.getElementById('palavra').value
			};
			service.radarSearch(request, callback);
		}

		function callback(results, status) {
			if (status !== google.maps.places.PlacesServiceStatus.OK) {
				console.error(status);
				return;
			}
			for (var i = 0, result; result = results[i]; i++) {
				addMarker(result);
			}
		}

		function addMarker(place) {
			var marker = new google.maps.Marker({
				map: map,
				position: place.geometry.location,
				icon: {
					url: 'http://maps.gstatic.com/mapfiles/circle.png',
					anchor: new google.maps.Point(10, 10),
					scaledSize: new google.maps.Size(10, 17)
				}
			});

			google.maps.event.addListener(marker, 'click', function() {
				service.getDetails(place, function(result, status) {
					if (status !== google.maps.places.PlacesServiceStatus.OK) {
						console.error(status);
						return;
					}
					infoWindow.setContent(result.name);
					infoWindow.open(map, marker);
					//Inserir aqui para gravar todos place_id, assim conforme vai carregando no mapa eu salvo no array
					// arr[i] = result.place_id;
					// contador++;
				});
			});
		}
		</script>
		<div class="container">
			<nav class="navbar navbar-default">
				<div class="container-fluid">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand">Painel de controle</a>
					</div>
					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						<ul class="nav navbar-nav">
							<li><a href="index.php">Início</a></li>
						</ul>
						<ul class="nav navbar-nav navbar-right">
							<li><a><?php echo "Consultas realizadas: ".$funcao->contaSaldo($cliente_id); ?></a></li>
							<li><a><?php if(!$funcao->validaPlano($cliente_id)) echo "Plano esgotado!"; ?></a></li>
							<li><a><?php echo $_SESSION['nome'] ?></a></li>
							<li><a href="logout.php">Sair</a></li>
						</ul>
					</div>
				</div>
			</nav>
			<br />
			<!-- Início do corpo -->
			<div class="row">
				<div class="span9">
					<div class="row">
						<!-- Form para pesquisa -->
						<div class="col-sm-4">
							<div class="form-group">
								<label for="exampleInputName2">Cidade</label><input type="text" id="address" name="address" class="form-control" placeholder="Curitiba" required="">
								<label for="exampleInputName2">Palavra chave</label><input type="text" id="palavra" name="palavra" class="form-control" placeholder="academia, mecanica"><br>
								<button id="submit" onclick="performSearch();" value="Geocode" class="btn btn-warning btn-primary btn-block">Buscar negócios</button>
							</div>
							<textarea id="textarea" cols="54" rows="4" style="resize:vertical;"></textarea><br>
							<button id="exportar" onclick="alert(arr.length);" class="btn btn-warning btn-primary btn-block">Limpar lista</button>
						</div>
						<div class="col-sm-8">
							<div id="map"></div>
						</div>
					</div>
					<!-- Ressultado do historico -->
					<br><br>
					<div class="col">
						<table class="table table-striped table-condensed">
							<thead>
								<tr>
									<th>#</th>
									<th>Palavra Chave</th>
									<th>Resultados</th>
									<th>Data</th>
									<th>Localidade</th>
									<th>Raio</th>
									<th>Download</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								while ($row = mysql_fetch_assoc($arquivos)) { 
									$link = "../_user/".$row['ARQUIVO'];
									?>
									<tr>
										<td><?php echo $row['ID']; ?></td>
										<td><?php echo $row['PALAVRA']; ?></td>
										<td><?php echo $row['QUANTIDADE']; ?></td>
										<td><?php echo $row['DATA']; ?></td>
										<td><?php echo $row['LOCALIDADE']; ?></td>
										<td><?php echo $row['RAIO']; ?></td>
										<td><a href="<?php echo $link; ?>">Click aqui para baixar</a></td>
									</tr>
									<?php }
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBi3ZJn4I7LonV2s4NA8xWtmA7bMgNqLeM&callback=initMap&signed_in=true&libraries=places,visualization" async defer></script>
		</body>
		</html>
		<?php
	}
	if (@$_GET['go'] == 'sair') {
		session_destroy();
		echo "<meta http-equiv='refresh' content='0, url=../login.php'>";
	}
	?>