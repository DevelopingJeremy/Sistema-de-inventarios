<?php

    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    iniSesion();
    validarSesion('../sesion/iniciar-sesion.php');
    inactividad('../sesion/iniciar-sesion.php');
    verifiCorreo('../sesion/envio-correo.php');

    $error = false;

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Empresa | Hybox</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/auth-styles.css">

</head>

<body>
    <div class="auth-container company-form">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-cube"></i>
                <h1>Hybox</h1>
            </div>
            <p class="auth-subtitle">Configura tu empresa</p>
            <p class="auth-description">Completa la información de tu empresa para personalizar tu experiencia</p>
        </div>

        <form action="../../src/empresa/guardar-empresa.php" method="POST" enctype="multipart/form-data" class="auth-form">
            <div class="form-row full-width">
                <div class="form-group">
                    <label class="form-label">Nombre de la empresa</label>
                    <input type="text" name="nombre" placeholder="Nombre de tu empresa" required class="form-input">
                </div>
            </div>

            <div class="form-row full-width">
                <div class="form-group">
                    <label class="form-label">Logo de la empresa (opcional)</label>
                    <div class="file-upload">
                        <input type="file" name="logo" accept="image/*" id="logoInput">
                        <label for="logoInput" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Haz clic para seleccionar un logo</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Cantidad de empleados</label>
                    <div class="select-wrapper">
                        <select name="empleados" id="empleados" required class="form-select">
                            <option value="">Seleccionar cantidad</option>
                            <option value="1-5">1 a 5 empleados</option>
                            <option value="6-15">6 a 15 empleados</option>
                            <option value="16-50">16 a 50 empleados</option>
                            <option value="51-100">51 a 100 empleados</option>
                            <option value="101-250">101 a 250 empleados</option>
                            <option value="251-500">251 a 500 empleados</option>
                            <option value="501-1000">501 a 1000 empleados</option>
                            <option value="1001+">Más de 1000 empleados</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <div class="select-wrapper">
                        <select name="categoria" id="categoriaSelect" onchange="mostrarCampoOtro()" required class="form-select">
                            <option value="">Seleccionar categoría</option>
                            <option value="Alimentos y bebidas">Alimentos y bebidas</option>
                            <option value="Electrónica">Electrónica</option>
                            <option value="Ferretería">Ferretería</option>
                            <option value="Supermercado">Supermercado</option>
                            <option value="Farmacia">Farmacia</option>
                            <option value="Papelería">Papelería</option>
                            <option value="Ropa y accesorios">Ropa y accesorios</option>
                            <option value="Tecnología">Tecnología</option>
                            <option value="Distribuidora">Distribuidora</option>
                            <option value="Limpieza e higiene">Limpieza e higiene</option>
                            <option value="Productos agrícolas">Productos agrícolas</option>
                            <option value="Automotriz">Automotriz</option>
                            <option value="Construcción">Construcción</option>
                            <option value="Veterinaria">Veterinaria</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="campoOtro" style="display: none;" class="form-row full-width">
                <div class="form-group">
                    <label class="form-label">Especificar otra categoría</label>
                    <input type="text" name="otra_categoria" id="otraCategoriaInput" placeholder="Describe tu categoría" class="form-input">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tipo de moneda</label>
                    <div class="select-wrapper">
                        <select name="moneda" id="moneda" required class="form-select">
                            <option value="">Seleccionar moneda</option>
                            <option value="USD">Dólar estadounidense (USD)</option>
                            <option value="EUR">Euro (EUR)</option>
                            <option value="CRC">Colón costarricense (CRC)</option>
                            <option value="MXN">Peso mexicano (MXN)</option>
                            <option value="COP">Peso colombiano (COP)</option>
                            <option value="ARS">Peso argentino (ARS)</option>
                            <option value="CLP">Peso chileno (CLP)</option>
                            <option value="PEN">Sol peruano (PEN)</option>
                            <option value="BOB">Boliviano (BOB)</option>
                            <option value="UYU">Peso uruguayo (UYU)</option>
                            <option value="PYG">Guaraní paraguayo (PYG)</option>
                            <option value="VES">Bolívar venezolano (VES)</option>
                            <option value="GTQ">Quetzal guatemalteco (GTQ)</option>
                            <option value="HNL">Lempira hondureño (HNL)</option>
                            <option value="NIO">Córdoba nicaragüense (NIO)</option>
                            <option value="DOP">Peso dominicano (DOP)</option>
                            <option value="PAB">Balboa panameño (PAB)</option>
                            <option value="CAD">Dólar canadiense (CAD)</option>
                            <option value="GBP">Libra esterlina (GBP)</option>
                            <option value="JPY">Yen japonés (JPY)</option>
                            <option value="CNY">Yuan chino (CNY)</option>
                            <option value="BRL">Real brasileño (BRL)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">País</label>
                    <div class="select-wrapper">
                        <select name="pais" id="pais" required class="form-select">
                            <option value="">Seleccionar país</option>
                            <option value="CR">🇨🇷 Costa Rica</option>
                            <option value="MX">🇲🇽 México</option>
                            <option value="CO">🇨🇴 Colombia</option>
                            <option value="AR">🇦🇷 Argentina</option>
                            <option value="CL">🇨🇱 Chile</option>
                            <option value="PE">🇵🇪 Perú</option>
                            <option value="BO">🇧🇴 Bolivia</option>
                            <option value="UY">🇺🇾 Uruguay</option>
                            <option value="PY">🇵🇾 Paraguay</option>
                            <option value="VE">🇻🇪 Venezuela</option>
                            <option value="GT">🇬🇹 Guatemala</option>
                            <option value="HN">🇭🇳 Honduras</option>
                            <option value="NI">🇳🇮 Nicaragua</option>
                            <option value="DO">🇩🇴 República Dominicana</option>
                            <option value="PA">🇵🇦 Panamá</option>
                            <option value="CA">🇨🇦 Canadá</option>
                            <option value="US">🇺🇸 Estados Unidos</option>
                            <option value="ES">🇪🇸 España</option>
                            <option value="BR">🇧🇷 Brasil</option>
                            <option value="JP">🇯🇵 Japón</option>
                            <option value="CN">🇨🇳 China</option>
                            <option value="GB">🇬🇧 Reino Unido</option>
                            <option value="DE">🇩🇪 Alemania</option>
                            <option value="FR">🇫🇷 Francia</option>
                            <option value="IT">🇮🇹 Italia</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-building"></i> Guardar Empresa
            </button>
        </form>


    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Función para mostrar/ocultar campo otra categoría
        function mostrarCampoOtro() {
            const categoriaSelect = document.getElementById('categoriaSelect');
            const campoOtro = document.getElementById('campoOtro');
            const otraCategoriaInput = document.getElementById('otraCategoriaInput');
            
            if (categoriaSelect.value === 'Otro') {
                campoOtro.style.display = 'block';
                otraCategoriaInput.required = true;
            } else {
                campoOtro.style.display = 'none';
                otraCategoriaInput.required = false;
                otraCategoriaInput.value = '';
            }
        }

        // Función para mostrar preview del logo
        function initLogoPreview() {
            const logoInput = document.getElementById('logoInput');
            if (logoInput) {
                logoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const label = document.querySelector('.file-upload-label');
                            label.innerHTML = `
                                <img src="${e.target.result}" style="max-width: 100px; max-height: 100px; border-radius: 8px; margin-bottom: 8px;">
                                <span>${file.name}</span>
                            `;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        }

        // Inicializar funcionalidades específicas de empresa
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar si hay un valor pre-seleccionado en la categoría
            const categoriaSelect = document.getElementById('categoriaSelect');
            if (categoriaSelect && categoriaSelect.value === 'Otro') {
                mostrarCampoOtro();
            }
            
            // Inicializar preview del logo
            initLogoPreview();
        });
    </script>
</body>

</html>