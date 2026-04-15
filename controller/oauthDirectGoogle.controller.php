<?php
/**
 * Controlador Google OAuth directo (sin HybridAuth).
 *
 * Flujo:
 * 1) Si no hay code -> redirige a Google con state
 * 2) Si vuelve con code -> intercambia token y obtiene perfil
 * 3) Login / vinculación / alta de usuario
 */

require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

try {
	if (!oauthGoogleConfigurado()) {
		throw new Exception('OAuth Google no configurado. Revisa GOOGLE_CLIENT_ID y GOOGLE_CLIENT_SECRET en env.php');
	}

	$redirectUri = defined('GOOGLE_REDIRECT_URI')
		? (string)GOOGLE_REDIRECT_URI
		: 'http://localhost/ProyecteServidor1/controller/googleOAuth.controller.php';

	// Paso 1: iniciar autorización
	if (!isset($_GET['code'])) {
		$state = bin2hex(random_bytes(16));
		$_SESSION['google_oauth_state'] = $state;

		$params = [
			'client_id' => GOOGLE_CLIENT_ID,
			'redirect_uri' => $redirectUri,
			'response_type' => 'code',
			'scope' => 'openid email profile',
			'state' => $state,
			'access_type' => 'online',
			'prompt' => 'select_account',
		];

		header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
		exit;
	}

	// Paso 2: validar state
	$state = isset($_GET['state']) ? (string)$_GET['state'] : '';
	if (!isset($_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'], $state)) {
		throw new Exception('Estado OAuth inválido. Inténtalo de nuevo.');
	}
	unset($_SESSION['google_oauth_state']);

	$code = (string)$_GET['code'];

	// Paso 3: intercambiar code por token
	$tokenResponse = httpPostForm('https://oauth2.googleapis.com/token', [
		'code' => $code,
		'client_id' => GOOGLE_CLIENT_ID,
		'client_secret' => GOOGLE_CLIENT_SECRET,
		'redirect_uri' => $redirectUri,
		'grant_type' => 'authorization_code',
	]);

	if (!isset($tokenResponse['access_token'])) {
		throw new Exception('No se pudo obtener access token de Google.');
	}

	$accessToken = (string)$tokenResponse['access_token'];

	// Paso 4: obtener perfil de usuario
	$userInfo = httpGetJson('https://www.googleapis.com/oauth2/v2/userinfo', [
		'Authorization: Bearer ' . $accessToken,
	]);

	$oauthUid = isset($userInfo['id']) ? (string)$userInfo['id'] : '';
	$email = isset($userInfo['email']) ? (string)$userInfo['email'] : '';
	$displayName = isset($userInfo['name']) ? (string)$userInfo['name'] : '';
	$givenName = isset($userInfo['given_name']) ? (string)$userInfo['given_name'] : '';
	$familyName = isset($userInfo['family_name']) ? (string)$userInfo['family_name'] : '';
	$photoUrl = isset($userInfo['picture']) ? (string)$userInfo['picture'] : '';

	if ($oauthUid === '' || $email === '') {
		throw new Exception('Google no devolvió identificador o email.');
	}

	$provider = 'Google';

	// Usuario ya vinculado por oauth_uid
	$usuarioExistente = obtenerUsuarioPorOAuthUID($provider, $oauthUid);
	if ($usuarioExistente) {
		actualizarOAuthToken((int)$usuarioExistente['id'], json_encode($tokenResponse));
		iniciarSesion($usuarioExistente);
		header('Location: ../index.php?success=' . urlencode('¡Bienvenido de nuevo, ' . $usuarioExistente['username'] . '!'));
		exit;
	}

	// Usuario existente por email -> vincular
	$usuarioPorEmail = obtenerUsuarioPorEmail($email);
	if ($usuarioPorEmail) {
		vincularOAuthAUsuario((int)$usuarioPorEmail['id'], $provider, $oauthUid, json_encode($tokenResponse));
		iniciarSesion($usuarioPorEmail);
		header('Location: ../index.php?success=' . urlencode('Cuenta de Google vinculada correctamente'));
		exit;
	}

	// Usuario nuevo
	$username = generarUsernameUnicoOAuth($displayName, $givenName, $familyName);
	$profileImage = 'userDefaultImg.jpg';
	if ($photoUrl !== '') {
		$profileImage = descargarImagenPerfilOAuth($photoUrl, $username);
	}

	$nuevoUserId = crearUsuarioOAuth($username, $email, $provider, $oauthUid, json_encode($tokenResponse), $profileImage);
	if (!$nuevoUserId) {
		throw new Exception('Error al crear la cuenta con Google.');
	}

	$nuevoUsuario = obtenerUsuarioPorId((int)$nuevoUserId);
	iniciarSesion($nuevoUsuario);
	header('Location: ../index.php?success=' . urlencode('¡Bienvenido a PokéNet, ' . $username . '!'));
	exit;

} catch (Exception $e) {
	error_log('Error Google OAuth: ' . $e->getMessage());
	header('Location: ../view/login.vista.php?error=' . urlencode('Error de autenticación con Google: ' . $e->getMessage()));
	exit;
}

function httpPostForm($url, array $fields) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);

	$raw = curl_exec($ch);
	if ($raw === false) {
		$err = curl_error($ch);
		curl_close($ch);
		throw new Exception('Fallo HTTP POST: ' . $err);
	}

	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	$data = json_decode($raw, true);
	if (!is_array($data)) {
		throw new Exception('Respuesta JSON inválida en POST OAuth.');
	}

	if ($status >= 400) {
		$msg = isset($data['error_description']) ? $data['error_description'] : (isset($data['error']) ? $data['error'] : 'HTTP ' . $status);
		throw new Exception('Error OAuth Google: ' . $msg);
	}

	return $data;
}

function httpGetJson($url, array $headers = []) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);

	if (!empty($headers)) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}

	$raw = curl_exec($ch);
	if ($raw === false) {
		$err = curl_error($ch);
		curl_close($ch);
		throw new Exception('Fallo HTTP GET: ' . $err);
	}

	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	$data = json_decode($raw, true);
	if (!is_array($data)) {
		throw new Exception('Respuesta JSON inválida en GET OAuth.');
	}

	if ($status >= 400) {
		$msg = isset($data['error']['message']) ? $data['error']['message'] : 'HTTP ' . $status;
		throw new Exception('Error al obtener perfil Google: ' . $msg);
	}

	return $data;
}

function generarUsernameUnicoOAuth($displayName, $firstName = '', $lastName = '') {
	$base = $displayName !== '' ? $displayName : trim($firstName . ' ' . $lastName);
	if ($base === '') {
		$base = 'usuario_google';
	}

	$base = preg_replace('/[^a-zA-Z0-9_]/', '_', $base);
	$base = strtolower($base);
	$base = substr($base, 0, 50);

	$username = $base;
	$contador = 1;

	while (existeUsername($username)) {
		$username = $base . '_' . $contador;
		$contador++;
	}

	return $username;
}

function descargarImagenPerfilOAuth($photoURL, $username) {
	try {
		$uploadDir = __DIR__ . '/../assets/img/imgProfileuser/';
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0755, true);
		}

		$nombreArchivo = $username . '_oauth_' . time() . '.jpg';
		$rutaCompleta = $uploadDir . $nombreArchivo;

		$contenidoImagen = @file_get_contents($photoURL);
		if ($contenidoImagen !== false) {
			file_put_contents($rutaCompleta, $contenidoImagen);
			return $nombreArchivo;
		}
	} catch (Exception $e) {
		error_log('Error al descargar imagen OAuth: ' . $e->getMessage());
	}

	return 'userDefaultImg.jpg';
}
