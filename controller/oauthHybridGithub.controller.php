<?php
/**
 * Controlador OAuth (HybridAuth)
 *
 * Este controlador queda dedicado al flujo de GitHub con HybridAuth.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../model/user/db_connection.php';
require_once __DIR__ . '/../model/user/account.model.php';
require_once __DIR__ . '/../model/user/oauth.model.php';
require_once __DIR__ . '/../security/auth.php';

use Hybridauth\Hybridauth;

try {
	// Cargar configuración de HybridAuth
	$config = require __DIR__ . '/../config/hybridauth.config.php';

	// Obtener el proveedor desde la URL (ej: ?provider=GitHub)
	// Si HybridAuth vuelve sin query, usamos GitHub por defecto.
	$provider = isset($_GET['provider']) ? $_GET['provider'] : 'GitHub';

	if (!$provider) {
		throw new Exception('Proveedor OAuth no especificado');
	}

	$proveedoresPermitidos = ['GitHub'];

	if (!in_array($provider, $proveedoresPermitidos, true)) {
		throw new Exception('Proveedor OAuth no soportado');
	}

	if ($provider === 'GitHub' && !oauthGithubConfigurado()) {
		throw new Exception('OAuth GitHub no configurado. Revisa GITHUB_CLIENT_ID y GITHUB_CLIENT_SECRET en env.php');
	}

	// Inicializar HybridAuth
	$hybridauth = new Hybridauth($config);

	// Intentar autenticar con el proveedor
	$adapter = $hybridauth->authenticate($provider);

	// Verificar si está conectado
	if (!$adapter->isConnected()) {
		throw new Exception('No se pudo conectar con ' . $provider);
	}

	// Obtener perfil del usuario desde el proveedor OAuth
	$userProfile = $adapter->getUserProfile();

	if (empty($userProfile->identifier)) {
		throw new Exception('No se pudo obtener el identificador del proveedor OAuth');
	}

	// Obtener token de acceso (opcional, para futuras llamadas a API)
	$accessToken = $adapter->getAccessToken();

	// Resolver email de forma robusta para GitHub:
	// 1) Perfil HybridAuth, 2) API /user/emails, 3) email tecnico estable.
	$email = trim((string)($userProfile->email ?? ''));
	if ($provider === 'GitHub' && $email === '') {
		$email = obtenerEmailGithubDesdeApi($adapter, $accessToken);
	}
	if ($provider === 'GitHub' && $email === '') {
		$email = construirEmailTecnicoGithub($userProfile);
	}
	if ($email === '') {
		throw new Exception('No se pudo resolver un email para completar la autenticacion OAuth.');
	}

	// Buscar si el usuario ya existe con este proveedor OAuth
	$usuarioExistente = obtenerUsuarioPorOAuthUID($provider, $userProfile->identifier);

	if ($usuarioExistente) {
		// Usuario ya existe, actualizar token y hacer login
		actualizarOAuthToken($usuarioExistente['id'], json_encode($accessToken));
		iniciarSesion($usuarioExistente);

		// Desconectar del proveedor OAuth
		$adapter->disconnect();

		// Redirigir a la página principal
		header('Location: ../index.php?success=' . urlencode('¡Bienvenido de nuevo, ' . $usuarioExistente['username'] . '!'));
		exit;
	}

	// Usuario nuevo: verificar si el email ya está registrado
	$usuarioPorEmail = obtenerUsuarioPorEmail($email);

	if ($usuarioPorEmail) {
		// Email ya existe, vincular cuenta OAuth
		vincularOAuthAUsuario(
			$usuarioPorEmail['id'],
			$provider,
			$userProfile->identifier,
			json_encode($accessToken)
		);

		iniciarSesion($usuarioPorEmail);

		$adapter->disconnect();

		header('Location: ../index.php?success=' . urlencode('Cuenta de ' . $provider . ' vinculada correctamente'));
		exit;
	}

	// Usuario completamente nuevo: crear cuenta
	$username = generarUsernameUnico($userProfile->displayName, $userProfile->firstName, $userProfile->lastName);
	$profileImage = 'userDefaultImg.jpg';

	// Descargar imagen de perfil si está disponible
	if (!empty($userProfile->photoURL)) {
		$profileImage = descargarImagenPerfil($userProfile->photoURL, $username);
	}

	// Crear usuario con OAuth
	$nuevoUserId = crearUsuarioOAuth(
		$username,
		$email,
		$provider,
		$userProfile->identifier,
		json_encode($accessToken),
		$profileImage
	);

	if ($nuevoUserId) {
		// Obtener el usuario recién creado
		$nuevoUsuario = obtenerUsuarioPorId($nuevoUserId);
		iniciarSesion($nuevoUsuario);

		$adapter->disconnect();

		header('Location: ../index.php?success=' . urlencode('¡Bienvenido a PokéNet, ' . $username . '!'));
		exit;
	} else {
		throw new Exception('Error al crear la cuenta');
	}

} catch (Exception $e) {
	// Registrar error en log
	error_log('Error OAuth GitHub HybridAuth: ' . $e->getMessage());

	// Redirigir con mensaje de error
	header('Location: ../view/login.vista.php?error=' . urlencode('Error de autenticación: ' . $e->getMessage()));
	exit;
}

/**
 * Genera un username único basado en el nombre del usuario
 */
function generarUsernameUnico($displayName, $firstName = '', $lastName = '') {
	// Prioridad: displayName > firstName + lastName
	$base = $displayName ?: ($firstName . ' ' . $lastName);

	// Limpiar el nombre: solo letras, números y guiones bajos
	$base = preg_replace('/[^a-zA-Z0-9_]/', '_', $base);
	$base = strtolower($base);
	$base = substr($base, 0, 50); // Máximo 50 caracteres

	$username = $base;
	$contador = 1;

	// Verificar si existe y agregar número si es necesario
	while (existeUsername($username)) {
		$username = $base . '_' . $contador;
		$contador++;
	}

	return $username;
}

/**
 * Descarga la imagen de perfil del usuario desde la URL del proveedor OAuth
 */
function descargarImagenPerfil($photoURL, $username) {
	try {
		// Directorio de imágenes de perfil
		$uploadDir = __DIR__ . '/../assets/img/imgProfileuser/';

		// Crear directorio si no existe
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0755, true);
		}

		// Nombre único para la imagen
		$extension = 'jpg';
		$nombreArchivo = $username . '_oauth_' . time() . '.' . $extension;
		$rutaCompleta = $uploadDir . $nombreArchivo;

		// Descargar imagen
		$contenidoImagen = @file_get_contents($photoURL);

		if ($contenidoImagen !== false) {
			file_put_contents($rutaCompleta, $contenidoImagen);
			return $nombreArchivo;
		}
	} catch (Exception $e) {
		error_log('Error al descargar imagen de perfil: ' . $e->getMessage());
	}

	// Si falla, usar imagen por defecto
	return 'userDefaultImg.jpg';
}

/**
 * Obtiene un email utilizable desde la API de GitHub cuando el perfil OAuth no lo trae.
 */
function obtenerEmailGithubDesdeApi($adapter, $accessToken) {
	$email = '';

	try {
		$response = $adapter->apiRequest('user/emails');
		$email = seleccionarMejorEmailGithub($response);
		if ($email !== '') {
			return $email;
		}
	} catch (Exception $e) {
		error_log('GitHub apiRequest user/emails fallo: ' . $e->getMessage());
	}

	$token = '';
	if (is_array($accessToken) && !empty($accessToken['access_token'])) {
		$token = (string)$accessToken['access_token'];
	}
	if ($token === '') {
		return '';
	}

	$response = githubApiGetUserEmails($token);
	return seleccionarMejorEmailGithub($response);
}

/**
 * Selecciona el mejor email posible del array devuelto por GitHub.
 */
function seleccionarMejorEmailGithub($emails) {
	if (!is_array($emails) || empty($emails)) {
		return '';
	}

	$candidatos = [];
	foreach ($emails as $item) {
		if (!is_array($item)) {
			continue;
		}

		$email = isset($item['email']) ? trim((string)$item['email']) : '';
		if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			continue;
		}

		$candidatos[] = [
			'email' => $email,
			'primary' => !empty($item['primary']),
			'verified' => !empty($item['verified'])
		];
	}

	if (empty($candidatos)) {
		return '';
	}

	foreach ($candidatos as $candidato) {
		if ($candidato['primary'] && $candidato['verified']) {
			return $candidato['email'];
		}
	}
	foreach ($candidatos as $candidato) {
		if ($candidato['verified']) {
			return $candidato['email'];
		}
	}
	foreach ($candidatos as $candidato) {
		if ($candidato['primary']) {
			return $candidato['email'];
		}
	}

	return $candidatos[0]['email'];
}

/**
 * Llamada HTTP simple a GitHub API para obtener emails del usuario.
 */
function githubApiGetUserEmails($token) {
	$url = 'https://api.github.com/user/emails';
	$headers = [
		'Accept: application/vnd.github+json',
		'Authorization: Bearer ' . $token,
		'User-Agent: PokeNetOAuth'
	];

	if (function_exists('curl_init')) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		$body = curl_exec($ch);
		$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($body === false || $httpCode < 200 || $httpCode >= 300) {
			return [];
		}

		$data = json_decode($body, true);
		return is_array($data) ? $data : [];
	}

	$context = stream_context_create([
		'http' => [
			'method' => 'GET',
			'timeout' => 10,
			'header' => implode("\r\n", $headers)
		]
	]);

	$body = @file_get_contents($url, false, $context);
	if ($body === false) {
		return [];
	}

	$data = json_decode($body, true);
	return is_array($data) ? $data : [];
}

/**
 * Construye un email tecnico estable para GitHub cuando no hay ninguno disponible.
 */
function construirEmailTecnicoGithub($userProfile) {
	$identifier = isset($userProfile->identifier) ? trim((string)$userProfile->identifier) : '';
	if ($identifier === '') {
		return '';
	}

	$username = isset($userProfile->displayName) ? trim((string)$userProfile->displayName) : '';
	$username = strtolower((string)preg_replace('/[^a-zA-Z0-9_]/', '_', $username));
	$username = trim($username, '_');
	if ($username === '') {
		$username = 'githubuser';
	}

	return 'github_' . $identifier . '_' . $username . '@users.noreply.local';
}
