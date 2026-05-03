<?php
function e($s){return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');}
$nombrePrefill = isset($_GET['titulo']) ? $_GET['titulo'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <base href="/ProyecteServidor1/">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Capturar Pokémon - PokéNet Social</title>
  <link rel="icon" type="image/jpeg" href="assets/img/fondo.jpg">
  <link rel="stylesheet" href="style/styles.css">
  <style>
    .container.form {
      max-width: 1140px;
      margin: 28px auto 88px;
      padding: 0 18px;
    }

    .header.form {
      margin-bottom: 18px;
      text-align: center;
    }

    .header.form h1.form {
      font-size: 2rem;
      letter-spacing: -0.03em;
      margin-bottom: 8px;
    }

    .subtitle {
      max-width: 760px;
      margin: 0 auto;
      color: #5d5d5d;
      font-size: 1rem;
      line-height: 1.5;
    }

    .form-container {
      border-radius: 28px;
      padding: 28px;
      background: rgba(255, 255, 255, 0.88);
      box-shadow: 0 20px 60px rgba(90, 0, 40, 0.14);
      border: 1px solid rgba(255, 0, 110, 0.08);
    }

    #pokemonForm {
      display: block;
    }

    .pokemon-api-panel {
      display: grid;
      grid-template-columns: minmax(0, 1.05fr) minmax(300px, 380px);
      gap: 28px;
      align-items: stretch;
    }

    .pokemon-search-box {
      position: relative;
    }

    .pokemon-suggestions {
      position: absolute;
      top: calc(100% + 10px);
      left: 0;
      right: 0;
      background: rgba(255, 255, 255, 0.98);
      border: 1px solid rgba(255, 0, 110, 0.12);
      border-radius: 16px;
      box-shadow: 0 16px 32px rgba(0, 0, 0, 0.16);
      max-height: 320px;
      overflow-y: auto;
      z-index: 20;
      display: none;
    }

    .pokemon-suggestion-item {
      display: flex;
      align-items: center;
      gap: 12px;
      width: 100%;
      padding: 12px 14px;
      border: 0;
      background: transparent;
      text-align: left;
      cursor: pointer;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .pokemon-suggestion-item:hover,
    .pokemon-suggestion-item.is-active {
      background: linear-gradient(135deg, rgba(255, 0, 110, 0.08), rgba(255, 190, 11, 0.08));
    }

    .pokemon-suggestion-sprite {
      width: 42px;
      height: 42px;
      object-fit: contain;
      flex-shrink: 0;
    }

    .pokemon-suggestion-info {
      min-width: 0;
      flex: 1;
    }

    .pokemon-suggestion-name {
      font-weight: 700;
      color: #1a1a1a;
    }

    .pokemon-suggestion-meta {
      font-size: 0.85rem;
      color: #777;
    }

    .pokemon-preview-card {
      border-radius: 24px;
      padding: 22px;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 249, 255, 0.96));
      border: 1px solid rgba(255, 0, 110, 0.10);
      box-shadow: 0 14px 30px rgba(0, 0, 0, 0.08);
      display: grid;
      gap: 18px;
      align-self: start;
      position: sticky;
      top: 110px;
    }

    .pokemon-preview-header {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .pokemon-preview-sprite {
      width: 88px;
      height: 88px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.8);
      object-fit: contain;
      border: 1px solid rgba(255, 0, 110, 0.12);
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.6);
    }

    .pokemon-preview-title {
      margin: 0;
      font-size: 1.2rem;
      color: #1a1a1a;
    }

    .pokemon-preview-subtitle {
      margin: 6px 0 0;
      color: #666;
      font-size: 0.93rem;
    }

    .pokemon-stats-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
    }

    .pokemon-stat {
      padding: 14px;
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.86);
      border: 1px solid rgba(255, 0, 110, 0.08);
    }

    .pokemon-stat-label {
      display: block;
      font-size: 0.76rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #888;
      margin-bottom: 6px;
    }

    .pokemon-stat-value {
      font-size: 1rem;
      font-weight: 700;
      color: #1a1a1a;
    }

    .pokemon-type-list {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .pokemon-type-badge {
      display: inline-flex;
      align-items: center;
      padding: 6px 10px;
      border-radius: 999px;
      background: rgba(255, 0, 110, 0.10);
      color: #b40050;
      font-size: 0.8rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .pokemon-helper {
      margin: 0;
      color: #6b6b6b;
      font-size: 0.92rem;
      line-height: 1.45;
    }

    .pokemon-readonly {
      background: rgba(245, 247, 251, 0.96);
      cursor: not-allowed;
    }

    .pokemon-loading {
      padding: 14px;
      color: #666;
      font-weight: 600;
      text-align: center;
    }

    @media (max-width: 960px) {
      .pokemon-api-panel {
        grid-template-columns: 1fr;
      }

      .pokemon-preview-card {
        position: static;
      }
    }

    @media (max-width: 640px) {
      .container.form {
        margin-top: 18px;
        padding: 0 12px;
      }

      .form-container {
        padding: 18px;
        border-radius: 22px;
      }

      .header.form h1.form {
        font-size: 1.7rem;
      }

      .pokemon-preview-card {
        padding: 18px;
      }

      .pokemon-preview-header {
        align-items: flex-start;
      }

      .pokemon-stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="navbar-container">
      <a href="index.php" class="navbar-brand" style="text-decoration: none;">🌟 PokéNet</a>
      <div class="navbar-actions">
          <span class="nav-user"><?= e($usuario['username']) ?></span>
          <a class="nav-btn" href="controller/logout.controller.php">Cerrar sesión</a>
      </div>
    </div>
  </nav>

  <div class="container form">
    <div class="header form">
      <h1 class="form">⚡ Capturar Pokémon</h1>
      <p class="subtitle">Busca un Pokémon en la API pública y completa sus datos automáticamente</p>
    </div>

    <?php if ($error): ?>
      <div class="alert error">❌ <?= e($error) ?></div>
    <?php endif; ?>

    <div class="form-container">
      <form action="controller/insertar.controller.php" method="post" id="pokemonForm" autocomplete="off">
        <?= csrfInput() ?>
        <input type="hidden" id="pokemon_api_name" name="pokemon_api_name" value="">

        <div class="pokemon-api-panel">
          <div>
            <div class="form-group pokemon-search-box">
              <label for="titulo">🎯 Nombre del Pokémon *</label>
              <input
                type="text"
                id="titulo"
                name="titulo"
                placeholder="Empieza a escribir, por ejemplo: pika, char, bulba..."
                value="<?= e($nombrePrefill) ?>"
                required
                autofocus
                autocomplete="off"
              >
              <div id="pokemonSuggestions" class="pokemon-suggestions" aria-live="polite"></div>
            </div>

            <div class="form-group">
              <label for="descripcion">📝 Descripción opcional</label>
              <textarea id="descripcion" name="descripcion" placeholder="Añade una nota, historia o comentario personal..."></textarea>
            </div>

            <div class="form-group">
              <label>📦 Datos autocompletados</label>
              <div class="pokemon-stats-grid">
                <div class="pokemon-stat">
                  <span class="pokemon-stat-label">Tipo principal</span>
                  <div class="pokemon-stat-value" id="tipoPrincipal">Selecciona un Pokémon</div>
                </div>
                <div class="pokemon-stat">
                  <span class="pokemon-stat-label">Tipo secundario</span>
                  <div class="pokemon-stat-value" id="tipoSecundario">-</div>
                </div>
                <div class="pokemon-stat">
                  <span class="pokemon-stat-label">Vida</span>
                  <div class="pokemon-stat-value" id="vida">-</div>
                </div>
                <div class="pokemon-stat">
                  <span class="pokemon-stat-label">Daño</span>
                  <div class="pokemon-stat-value" id="ataque">-</div>
                </div>
                <div class="pokemon-stat">
                  <span class="pokemon-stat-label">Defensa</span>
                  <div class="pokemon-stat-value" id="defensa">-</div>
                </div>
                <div class="pokemon-stat">
                  <span class="pokemon-stat-label">Velocidad</span>
                  <div class="pokemon-stat-value" id="velocidad">-</div>
                </div>
              </div>
            </div>
          </div>

          <aside class="pokemon-preview-card">
            <div class="pokemon-preview-header">
              <img id="pokemonSprite" class="pokemon-preview-sprite" src="assets/img/fondo.jpg" alt="Vista previa de Pokémon">
              <div>
                <h2 class="pokemon-preview-title" id="pokemonPreviewTitle">Busca y selecciona un Pokémon</h2>
                <p class="pokemon-preview-subtitle" id="pokemonPreviewSubtitle">Los campos de la derecha se completarán automáticamente</p>
              </div>
            </div>

            <p class="pokemon-helper">El nombre sirve para buscar en la API de Pokémon. Cuando pulses sobre una sugerencia, la aplicación cargará sus estadísticas y los tipos sin que tengas que escribirlos a mano.</p>

            <div class="form-group">
              <label>Tipos detectados</label>
              <div class="pokemon-type-list" id="pokemonTypesList">
                <span class="pokemon-type-badge">Pendiente</span>
              </div>
            </div>

            <div class="form-group">
              <label>Estado de selección</label>
              <input type="text" class="pokemon-readonly" id="pokemonSelectionState" value="Todavía no has elegido un Pokémon" readonly>
            </div>
          </aside>
        </div>

        <div class="actions form" style="margin-top: 24px;">
          <button class="btn primary" type="submit">🏆 Capturar Pokémon</button>
          <a class="btn secondary" href="index.php">🔙 Cancelar</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    const tituloInput = document.getElementById('titulo');
    const suggestionsBox = document.getElementById('pokemonSuggestions');
    const pokemonApiNameInput = document.getElementById('pokemon_api_name');
    const pokemonSprite = document.getElementById('pokemonSprite');
    const pokemonPreviewTitle = document.getElementById('pokemonPreviewTitle');
    const pokemonPreviewSubtitle = document.getElementById('pokemonPreviewSubtitle');
    const pokemonSelectionState = document.getElementById('pokemonSelectionState');
    const tipoPrincipal = document.getElementById('tipoPrincipal');
    const tipoSecundario = document.getElementById('tipoSecundario');
    const vida = document.getElementById('vida');
    const ataque = document.getElementById('ataque');
    const defensa = document.getElementById('defensa');
    const velocidad = document.getElementById('velocidad');
    const pokemonTypesList = document.getElementById('pokemonTypesList');

    let searchTimeout = null;

    function normalizeTitle(text) {
      return (text || '')
        .toString()
        .trim()
        .replace(/\s+/g, ' ');
    }

    function resetPreviewState() {
      pokemonApiNameInput.value = '';
      pokemonSprite.src = 'assets/img/fondo.jpg';
      pokemonSprite.alt = 'Vista previa de Pokémon';
      pokemonPreviewTitle.textContent = 'Busca y selecciona un Pokémon';
      pokemonPreviewSubtitle.textContent = 'Los campos de la derecha se completarán automáticamente';
      pokemonSelectionState.value = 'Todavía no has elegido un Pokémon';
      tipoPrincipal.textContent = 'Selecciona un Pokémon';
      tipoSecundario.textContent = '-';
      vida.textContent = '-';
      ataque.textContent = '-';
      defensa.textContent = '-';
      velocidad.textContent = '-';
      pokemonTypesList.innerHTML = '<span class="pokemon-type-badge">Pendiente</span>';
    }

    function renderSuggestions(items) {
      if (!items.length) {
        suggestionsBox.innerHTML = '<div class="pokemon-loading">No hay coincidencias en la API pública</div>';
        suggestionsBox.style.display = 'block';
        return;
      }

      suggestionsBox.innerHTML = items.map(function(item) {
        const sprite = item.sprite_url || 'assets/img/fondo.jpg';
        return `
          <button type="button" class="pokemon-suggestion-item" data-name="${item.name}" data-display-name="${item.display_name}">
            <img class="pokemon-suggestion-sprite" src="${sprite}" alt="${item.display_name}" onerror="this.src='assets/img/fondo.jpg'">
            <div class="pokemon-suggestion-info">
              <div class="pokemon-suggestion-name">${item.display_name}</div>
              <div class="pokemon-suggestion-meta">#${item.api_id || '-'} · ${item.name}</div>
            </div>
          </button>
        `;
      }).join('');

      suggestionsBox.style.display = 'block';

      suggestionsBox.querySelectorAll('.pokemon-suggestion-item').forEach(function(button) {
        button.addEventListener('click', function() {
          cargarPokemonSeleccionado(this.dataset.name, this.dataset.displayName);
        });
      });
    }

    async function buscarPokemon(query) {
      const texto = normalizeTitle(query);
      if (texto.length < 2) {
        suggestionsBox.style.display = 'none';
        suggestionsBox.innerHTML = '';
        return;
      }

      suggestionsBox.innerHTML = '<div class="pokemon-loading">Buscando en la API pública...</div>';
      suggestionsBox.style.display = 'block';

      try {
        const response = await fetch(`controller/pokemonApi.controller.php?q=${encodeURIComponent(texto)}`);
        const data = await response.json();

        if (data.success && Array.isArray(data.results)) {
          renderSuggestions(data.results);
        } else {
          suggestionsBox.innerHTML = '<div class="pokemon-loading">No se encontraron resultados</div>';
          suggestionsBox.style.display = 'block';
        }
      } catch (error) {
        suggestionsBox.innerHTML = '<div class="pokemon-loading">No se pudo conectar con la API pública</div>';
        suggestionsBox.style.display = 'block';
      }
    }

    async function cargarPokemonSeleccionado(apiName, displayName) {
      suggestionsBox.style.display = 'none';
      suggestionsBox.innerHTML = '';
      pokemonApiNameInput.value = apiName;
      tituloInput.value = displayName || apiName;
      pokemonPreviewSubtitle.textContent = 'Cargando datos oficiales de la API...';
      pokemonSelectionState.value = 'Cargando datos de ' + (displayName || apiName) + '...';

      try {
        const response = await fetch(`controller/pokemonApi.controller.php?name=${encodeURIComponent(apiName)}`);
        const data = await response.json();

        if (!data.success || !data.pokemon) {
          pokemonSelectionState.value = 'No se pudo cargar el Pokémon seleccionado';
          return;
        }

        const pokemon = data.pokemon;
        pokemonApiNameInput.value = pokemon.api_name || apiName;
        tituloInput.value = pokemon.display_name || displayName || apiName;
        pokemonSprite.src = pokemon.sprite_url || 'assets/img/fondo.jpg';
        pokemonSprite.alt = pokemon.display_name || apiName;
        pokemonPreviewTitle.textContent = pokemon.display_name || apiName;
        pokemonPreviewSubtitle.textContent = 'Datos oficiales cargados desde la API pública';
        pokemonSelectionState.value = `Pokémon seleccionado: ${pokemon.display_name || apiName}`;

        tipoPrincipal.textContent = pokemon.primary_type || '-';
        tipoSecundario.textContent = pokemon.secondary_type || '-';
        vida.textContent = pokemon.vida !== null && pokemon.vida !== undefined ? pokemon.vida : '-';
        ataque.textContent = pokemon.ataque !== null && pokemon.ataque !== undefined ? pokemon.ataque : '-';
        defensa.textContent = pokemon.defensa !== null && pokemon.defensa !== undefined ? pokemon.defensa : '-';
        velocidad.textContent = pokemon.velocidad !== null && pokemon.velocidad !== undefined ? pokemon.velocidad : '-';

        const types = Array.isArray(pokemon.types) && pokemon.types.length > 0 ? pokemon.types : [pokemon.primary_type].filter(Boolean);
        pokemonTypesList.innerHTML = types.length
          ? types.map(function(typeName) {
              return `<span class="pokemon-type-badge">${typeName}</span>`;
            }).join('')
          : '<span class="pokemon-type-badge">Sin tipos</span>';
      } catch (error) {
        pokemonSelectionState.value = 'No se pudieron cargar los datos oficiales';
      }
    }

    tituloInput.addEventListener('input', function() {
      pokemonApiNameInput.value = '';
      pokemonSelectionState.value = 'Todavía no has elegido un Pokémon';
      pokemonPreviewTitle.textContent = 'Busca y selecciona un Pokémon';
      pokemonPreviewSubtitle.textContent = 'Los campos de la derecha se completarán automáticamente';
      pokemonSprite.src = 'assets/img/fondo.jpg';
      tipoPrincipal.textContent = 'Selecciona un Pokémon';
      tipoSecundario.textContent = '-';
      vida.textContent = '-';
      ataque.textContent = '-';
      defensa.textContent = '-';
      velocidad.textContent = '-';
      pokemonTypesList.innerHTML = '<span class="pokemon-type-badge">Pendiente</span>';

      if (searchTimeout) {
        clearTimeout(searchTimeout);
      }

      searchTimeout = setTimeout(function() {
        buscarPokemon(tituloInput.value);
      }, 300);
    });

    tituloInput.addEventListener('focus', function() {
      if (tituloInput.value.trim().length >= 2 && suggestionsBox.innerHTML.trim() !== '') {
        suggestionsBox.style.display = 'block';
      }
    });

    document.addEventListener('click', function(event) {
      if (!event.target.closest('.pokemon-search-box')) {
        suggestionsBox.style.display = 'none';
      }
    });

    document.getElementById('pokemonForm').addEventListener('submit', function(event) {
      if (!pokemonApiNameInput.value) {
        event.preventDefault();
        pokemonSelectionState.value = 'Selecciona un Pokémon de la lista antes de enviar';
      }
    });

    resetPreviewState();

    <?php if ($nombrePrefill): ?>
      buscarPokemon(<?= json_encode($nombrePrefill, JSON_UNESCAPED_UNICODE) ?>);
    <?php endif; ?>
  </script>
</body>
</html>