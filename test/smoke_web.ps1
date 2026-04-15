param(
    [string]$BaseUrl = 'http://localhost/ProyecteServidor1'
)

$ErrorActionPreference = 'Stop'

$base = $BaseUrl.TrimEnd('/')
$results = New-Object System.Collections.Generic.List[object]
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

function Add-Result($name, $ok, $detail) {
    $results.Add([pscustomobject]@{ Test = $name; Ok = $ok; Detail = $detail })
}

function Get-CsrfToken([string]$html) {
    $m = [regex]::Match($html, 'name="_csrf_token"\s+value="([^"]+)"')
    if (-not $m.Success) {
        throw 'No se pudo extraer _csrf_token del HTML.'
    }
    return $m.Groups[1].Value
}

function Request-Page([string]$url) {
    return Invoke-WebRequest -Uri $url -WebSession $session -UseBasicParsing
}

try {
    # 1) Home
    $home = Request-Page "$base/"
    Add-Result 'Home carga' $true "HTTP $($home.StatusCode)"

    # Datos de usuario de prueba
    $stamp = Get-Date -Format 'yyyyMMddHHmmss'
    $username = "smoke_$stamp"
    $email = "$username@example.com"
    $password = 'Aa123456'
    $newPassword = 'Bb123456'
    $postTitle = "SmokePost_$stamp"
    $postTitleEdited = "SmokePostEdit_$stamp"

    # 2) Registro
    $registerPage = Request-Page "$base/view/register.vista.php"
    $csrfRegister = Get-CsrfToken $registerPage.Content
    $registerResp = Invoke-WebRequest -Uri "$base/controller/register.controller.php" -Method Post -WebSession $session -UseBasicParsing -Body @{
        _csrf_token = $csrfRegister
        username = $username
        email = $email
        password = $password
        password2 = $password
    }
    if ($registerResp.Content -match [regex]::Escape($username)) {
        Add-Result 'Registro usuario' $true "Usuario $username registrado e identificado"
    } else {
        Add-Result 'Registro usuario' $false 'No se detectó usuario logueado tras registro.'
    }

    # 3) Insertar publicación
    $insertPage = Request-Page "$base/controller/insertarPage.controller.php"
    $csrfInsert = Get-CsrfToken $insertPage.Content
    $insertResp = Invoke-WebRequest -Uri "$base/controller/insertar.controller.php" -Method Post -WebSession $session -UseBasicParsing -Body @{
        _csrf_token = $csrfInsert
        titulo = $postTitle
        descripcion = 'Descripcion smoke test'
    }
    if ($insertResp.Content -match [regex]::Escape($postTitle)) {
        Add-Result 'Crear publicación' $true "Publicación $postTitle creada"
    } else {
        Add-Result 'Crear publicación' $false 'No apareció el título tras insertar.'
    }

    # Obtener id propio desde index
    $myIdMatch = [regex]::Match($insertResp.Content, 'perfilUsuarioPage\.controller\.php\?id=(\d+)')
    if (-not $myIdMatch.Success) { throw 'No se pudo obtener id de usuario actual.' }
    $myId = [int]$myIdMatch.Groups[1].Value

    # 4) Ver mi perfil
    $myProfile = Request-Page "$base/controller/perfilUsuarioPage.controller.php?id=$myId"
    $myProfileOk = $myProfile.Content -match [regex]::Escape($postTitle)
    Add-Result 'Ver mi perfil' $myProfileOk "Perfil id=$myId"

    # Obtener id publicación (primer editar del perfil)
    $postIdMatch = [regex]::Match($myProfile.Content, 'modificarPage\.controller\.php\?id=(\d+)')
    if (-not $postIdMatch.Success) { throw 'No se pudo obtener id de publicación para editar.' }
    $postId = [int]$postIdMatch.Groups[1].Value

    # 5) Editar publicación
    $editPage = Request-Page "$base/controller/modificarPage.controller.php?id=$postId"
    $csrfEdit = Get-CsrfToken $editPage.Content
    $editResp = Invoke-WebRequest -Uri "$base/controller/modificar.controller.php" -Method Post -WebSession $session -UseBasicParsing -Body @{
        _csrf_token = $csrfEdit
        id = $postId
        titulo = $postTitleEdited
        descripcion = 'Descripcion editada smoke test'
    }
    $editOk = $editResp.Content -match [regex]::Escape($postTitleEdited)
    Add-Result 'Editar publicación' $editOk "Post id=$postId"

    # 6) Ver perfil de otro usuario
    $otherId = if ($myId -eq 1) { 2 } else { 1 }
    $otherProfile = Request-Page "$base/controller/perfilUsuarioPage.controller.php?id=$otherId"
    $otherOk = ($otherProfile.StatusCode -eq 200) -or ($otherProfile.BaseResponse.ResponseUri.AbsoluteUri -like '*index.php*')
    Add-Result 'Ver perfil de otro usuario' $otherOk "Intento id=$otherId"

    # 7) Editar perfil (solo username)
    $editProfilePage = Request-Page "$base/controller/modificarPerfilPage.controller.php"
    $csrfEditProfile = Get-CsrfToken $editProfilePage.Content
    $newUsername = "${username}_ed"
    $editProfileResp = Invoke-WebRequest -Uri "$base/controller/modificarPerfil.controller.php" -Method Post -WebSession $session -UseBasicParsing -Body @{
        _csrf_token = $csrfEditProfile
        username = $newUsername
    }
    $editProfileOk = $editProfileResp.Content -match [regex]::Escape($newUsername)
    Add-Result 'Editar perfil' $editProfileOk "Nuevo username=$newUsername"

    # 8) Cambiar contraseña
    $changePassPage = Request-Page "$base/controller/cambiarContrasenaPage.controller.php"
    $csrfChange = Get-CsrfToken $changePassPage.Content
    $changePassResp = Invoke-WebRequest -Uri "$base/controller/cambiarContrasena.controller.php" -Method Post -WebSession $session -UseBasicParsing -Body @{
        _csrf_token = $csrfChange
        current_password = $password
        new_password = $newPassword
        confirm_password = $newPassword
    }
    $changePassOk = $changePassResp.Content -match 'Contraseña actualizada correctamente'
    Add-Result 'Cambiar contraseña' $changePassOk 'Cambio de password ejecutado'

    # 9) Logout
    $null = Request-Page "$base/controller/logout.controller.php"

    # 10) Login con usuario/contraseña
    $loginPage = Request-Page "$base/view/login.vista.php"
    $csrfLogin = Get-CsrfToken $loginPage.Content
    $loginResp = Invoke-WebRequest -Uri "$base/controller/login.controller.php" -Method Post -WebSession $session -UseBasicParsing -Body @{
        _csrf_token = $csrfLogin
        user = $newUsername
        password = $newPassword
    }
    $loginOk = $loginResp.Content -match [regex]::Escape($newUsername)
    Add-Result 'Login con usuario/contraseña' $loginOk "Login con $newUsername"

    # 11) Recuperar contraseña (solicitud)
    $recoverPage = Request-Page "$base/view/recuperarContrasena.vista.php"
    $csrfRecover = Get-CsrfToken $recoverPage.Content
    $recoverResp = Invoke-WebRequest -Uri "$base/controller/solicitarRecuperacion.controller.php" -Method Post -WebSession $session -UseBasicParsing -Body @{
        _csrf_token = $csrfRecover
        email = $email
    }
    $recoverOk = ($recoverResp.Content -match 'Se ha mandado un correo') -or ($recoverResp.Content -match 'Error PHPMailer')
    Add-Result 'Solicitar recuperación contraseña' $recoverOk 'Flujo ejecutado (éxito de correo o error SMTP controlado)'

    # 12) Eliminar publicación (la editada)
    $profileForDelete = Request-Page "$base/controller/perfilUsuarioPage.controller.php?id=$myId"
    $csrfDelete = Get-CsrfToken $profileForDelete.Content
    $deleteResp = Invoke-WebRequest -Uri "$base/controller/eliminar.controller.php" -Method Post -WebSession $session -UseBasicParsing -Body @{
        _csrf_token = $csrfDelete
        id = $postId
    }
    $deleteOk = -not ($deleteResp.Content -match [regex]::Escape($postTitleEdited))
    Add-Result 'Eliminar publicación' $deleteOk "Post id=$postId"

} catch {
    Add-Result 'Ejecución general' $false $_.Exception.Message
}

$table = $results | Format-Table -AutoSize | Out-String -Width 220
$json = $results | ConvertTo-Json -Depth 4

$table | Write-Output
"JSON_RESULTS:" | Write-Output
$json | Write-Output

$outPath = Join-Path $PSScriptRoot 'smoke_web_result.json'
$json | Set-Content -Path $outPath -Encoding UTF8
