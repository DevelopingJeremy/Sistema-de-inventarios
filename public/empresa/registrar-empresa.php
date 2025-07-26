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
    <title>Registrar Empresa</title>

</head>

<body>

    <h2>Registra tu empresa!</h2>
    <form action="../../src/empresa/guardar-empresa.php" method="POST" enctype="multipart/form-data">
        <label>Nombre de la empresa:</label><br>
        <input type="text" name="nombre" required><br><br>

        <label>Logo (opcional):</label><br>
        <input type="file" name="logo" accept="image/*"><br><br>

        <label>Cantidad aproximada de empleados:</label><br>
        <select name="empleados" id="empleados" required>
            <option value="">-- Numero de empleados --</option>
            <option value="1-5">1 a 5 empleados</option>
            <option value="6-15">6 a 15 empleados</option>
            <option value="16-50">16 a 50 empleados</option>
            <option value="51-100">51 a 100 empleados</option>
            <option value="101-250">101 a 250 empleados</option>
            <option value="251-500">251 a 500 empleados</option>
            <option value="501-1000">501 a 1000 empleados</option>
            <option value="1001+">Más de 1000 empleados</option>
        </select>
        <br><br>

        <label>Categoría:</label><br>
        <select name="categoria" id="categoriaSelect" onchange="mostrarCampoOtro()" required>
            <option value="">-- Seleccionar categoría --</option>
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
        </select><br><br>

        <div id="campoOtro" style="display: none;">
            <label>Especificar otra categoría:</label><br>
            <input type="text" name="otra_categoria" id="otraCategoriaInput"><br><br>
        </div>

        <label>Tipo de moneda:</label><br>
        <select name="moneda" id="moneda" required>
            <option value="">-- Tipo de moneda --</option>
            <option value="AFN">Afgani afgano (AFN)</option>
            <option value="THB">Baht tailandés (THB)</option>
            <option value="VES">Bolívar venezolano (VES)</option>
            <option value="BOB">Boliviano (BOB)</option>
            <option value="GHS">Cedi ghanés (GHS)</option>
            <option value="NIO">Córdoba nicaragüense (NIO)</option>
            <option value="CRC">Colón costarricense (CRC)</option>
            <option value="CZK">Corona checa (CZK)</option>
            <option value="DKK">Corona danesa (DKK)</option>
            <option value="ISK">Corona islandesa (ISK)</option>
            <option value="NOK">Corona noruega (NOK)</option>
            <option value="SEK">Corona sueca (SEK)</option>
            <option value="DZD">Dinar argelino (DZD)</option>
            <option value="BHD">Dinar bahreiní (BHD)</option>
            <option value="IQD">Dinar iraquí (IQD)</option>
            <option value="JOD">Dinar jordano (JOD)</option>
            <option value="KWD">Dinar kuwaití (KWD)</option>
            <option value="RSD">Dinar serbio (RSD)</option>
            <option value="TND">Dinar tunecino (TND)</option>
            <option value="AUD">Dólar australiano (AUD)</option>
            <option value="BSD">Dólar bahameño (BSD)</option>
            <option value="BBD">Dólar barbadense (BBD)</option>
            <option value="BZD">Dólar beliceño (BZD)</option>
            <option value="BMD">Dólar bermudeño (BMD)</option>
            <option value="CAD">Dólar canadiense (CAD)</option>
            <option value="SBD">Dólar de las Islas Salomón (SBD)</option>
            <option value="XCD">Dólar del Caribe Oriental (XCD)</option>
            <option value="DOP">Peso dominicano (DOP)</option>
            <option value="ARS">Peso argentino (ARS)</option>
            <option value="CLP">Peso chileno (CLP)</option>
            <option value="COP">Peso colombiano (COP)</option>
            <option value="PHP">Peso filipino (PHP)</option>
            <option value="MXN">Peso mexicano (MXN)</option>
            <option value="UYU">Peso uruguayo (UYU)</option>
            <option value="PEN">Sol peruano (PEN)</option>
            <option value="QAR">Rial catarí (QAR)</option>
            <option value="IRR">Rial iraní (IRR)</option>
            <option value="YER">Rial yemení (YER)</option>
            <option value="SAR">Riyal saudí (SAR)</option>
            <option value="CNY">Yuan chino (CNY)</option>
            <option value="HKD">Dólar de Hong Kong (HKD)</option>
            <option value="HNL">Lempira hondureño (HNL)</option>
            <option value="HRK">Kuna croata (HRK)</option>
            <option value="HUF">Forinto húngaro (HUF)</option>
            <option value="IDR">Rupia indonesia (IDR)</option>
            <option value="INR">Rupia india (INR)</option>
            <option value="LKR">Rupia de Sri Lanka (LKR)</option>
            <option value="MUR">Rupia mauriciana (MUR)</option>
            <option value="NPR">Rupia nepalí (NPR)</option>
            <option value="PKR">Rupia pakistaní (PKR)</option>
            <option value="SCR">Rupia seychellense (SCR)</option>
            <option value="RUB">Rublo ruso (RUB)</option>
            <option value="BYN">Rublo bielorruso (BYN)</option>
            <option value="SZL">Lilangeni suazi (SZL)</option>
            <option value="LTL">Litas lituano (LTL)</option>
            <option value="LVL">Lats letón (LVL)</option>
            <option value="MDL">Leu moldavo (MDL)</option>
            <option value="RON">Leu rumano (RON)</option>
            <option value="SLL">Leone sierraleonés (SLL)</option>
            <option value="GIP">Libra de Gibraltar (GIP)</option>
            <option value="EGP">Libra egipcia (EGP)</option>
            <option value="SDG">Libra sudanesa (SDG)</option>
            <option value="SYP">Libra siria (SYP)</option>
            <option value="LBP">Libra libanesa (LBP)</option>
            <option value="GBP">Libra esterlina (GBP)</option>
            <option value="MGA">Ariary malgache (MGA)</option>
            <option value="MKD">Denar macedonio (MKD)</option>
            <option value="MMK">Kyat birmano (MMK)</option>
            <option value="MNT">Tugrik mongol (MNT)</option>
            <option value="MOP">Pataca macanesa (MOP)</option>
            <option value="MRU">Ouguiya mauritano (MRU)</option>
            <option value="MYR">Ringgit malasio (MYR)</option>
            <option value="ZMW">Kwacha zambiano (ZMW)</option>
            <option value="KES">Chelín keniano (KES)</option>
            <option value="SOS">Chelín somalí (SOS)</option>
            <option value="TZS">Chelín tanzano (TZS)</option>
            <option value="UGX">Chelín ugandés (UGX)</option>
            <option value="CHF">Franco suizo (CHF)</option>
            <option value="BIF">Franco burundés (BIF)</option>
            <option value="XAF">Franco CFA BEAC (XAF)</option>
            <option value="XOF">Franco CFA BCEAO (XOF)</option>
            <option value="XPF">Franco CFP (XPF)</option>
            <option value="FJD">Dólar fiyiano (FJD)</option>
            <option value="FOK">Corona feroesa (FOK)</option>
            <option value="GEL">Lari georgiano (GEL)</option>
            <option value="GMD">Dalasi gambiano (GMD)</option>
            <option value="GNF">Franco guineano (GNF)</option>
            <option value="GTQ">Quetzal guatemalteco (GTQ)</option>
            <option value="GYD">Dólar guyanés (GYD)</option>
            <option value="HUF">Forinto húngaro (HUF)</option>
            <option value="ILS">Nuevo shéquel israelí (ILS)</option>
            <option value="JMD">Dólar jamaicano (JMD)</option>
            <option value="JOD">Dinar jordano (JOD)</option>
            <option value="JPY">Yen japonés (JPY)</option>
            <option value="KES">Chelín keniano (KES)</option>
            <option value="KGS">Som kirguís (KGS)</option>
            <option value="KHR">Riel camboyano (KHR)</option>
            <option value="KMF">Franco comorense (KMF)</option>
            <option value="KRW">Won surcoreano (KRW)</option>
            <option value="KWD">Dinar kuwaití (KWD)</option>
            <option value="KYD">Dólar de las Islas Caimán (KYD)</option>
            <option value="KZT">Tenge kazajo (KZT)</option>
            <option value="LAK">Kip laosiano (LAK)</option>
            <option value="LBP">Libra libanesa (LBP)</option>
            <option value="LKR">Rupia de Sri Lanka (LKR)</option>
            <option value="LRD">Dólar liberiano (LRD)</option>
            <option value="LSL">Loti lesotense (LSL)</option>
            <option value="LYD">Dinar libio (LYD)</option>
            <option value="MAD">Dirham marroquí (MAD)</option>
            <option value="MDL">Leu moldavo (MDL)</option>
            <option value="MGA">Ariary malgache (MGA)</option>
            <option value="MKD">Denar macedonio (MKD)</option>
            <option value="MMK">Kyat birmano (MMK)</option>
            <option value="MNT">Tugrik mongol (MNT)</option>
            <option value="MOP">Pataca macanesa (MOP)</option>
            <option value="MRU">Ouguiya mauritano (MRU)</option>
            <option value="MUR">Rupia mauriciana (MUR)</option>
            <option value="MVR">Rufiyaa maldiva (MVR)</option>
            <option value="MWK">Kwacha malauí (MWK)</option>
            <option value="MXN">Peso mexicano (MXN)</option>
            <option value="MYR">Ringgit malasio (MYR)</option>
            <option value="MZN">Metical mozambiqueño (MZN)</option>
            <option value="NAD">Dólar namibio (NAD)</option>
            <option value="NGN">Naira nigeriana (NGN)</option>
            <option value="NIO">Córdoba nicaragüense (NIO)</option>
            <option value="NOK">Corona noruega (NOK)</option>
            <option value="NPR">Rupia nepalí (NPR)</option>
            <option value="NZD">Dólar neozelandés (NZD)</option>
            <option value="OMR">Rial omaní (OMR)</option>
            <option value="PAB">Balboa panameño (PAB)</option>
            <option value="PEN">Sol peruano (PEN)</option>
            <option value="PGK">Kina papuana (PGK)</option>
            <option value="PHP">Peso filipino (PHP)</option>
            <option value="PKR">Rupia pakistaní (PKR)</option>
            <option value="PLN">Zloty polaco (PLN)</option>
            <option value="PYG">Guaraní paraguayo (PYG)</option>
            <option value="QAR">Rial catarí (QAR)</option>
            <option value="RON">Leu rumano (RON)</option>
            <option value="RSD">Dinar serbio (RSD)</option>
            <option value="RUB">Rublo ruso (RUB)</option>
            <option value="SAR">Riyal saudí (SAR)</option>
            <option value="SBD">Dólar de las Islas Salomón (SBD)</option>
            <option value="SCR">Rupia seychellense (SCR)</option>
            <option value="SDG">Libra sudanesa (SDG)</option>
            <option value="SEK">Corona sueca (SEK)</option>
            <option value="SGD">Dólar singapurense (SGD)</option>
            <option value="SHP">Libra de Santa Elena (SHP)</option>
            <option value="SLL">Leone sierraleonés (SLL)</option>
            <option value="SOS">Chelín somalí (SOS)</option>
            <option value="SRD">Dólar surinamés (SRD)</option>
            <option value="STN">Dobra de Santo Tomé y Príncipe (STN)</option>
            <option value="SYP">Libra siria (SYP)</option>
            <option value="SZL">Lilangeni suazi (SZL)</option>
            <option value="THB">Baht tailandés (THB)</option>
            <option value="TJS">Somoni tayiko (TJS)</option>
            <option value="TMT">Manat turcomano (TMT)</option>
            <option value="TND">Dinar tunecino (TND)</option>
            <option value="TRY">Lira turca (TRY)</option>
            <option value="TTD">Dólar trinitense (TTD)</option>
            <option value="TWD">Nuevo dólar taiwanés (TWD)</option>
            <option value="TZS">Chelín tanzano (TZS)</option>
            <option value="UAH">Hryvnia ucraniana (UAH)</option>
            <option value="UGX">Chelín ugandés (UGX)</option>
            <option value="USD">Dólar estadounidense (USD)</option>
            <option value="UYU">Peso uruguayo (UYU)</option>
            <option value="UZS">Sum uzbeko (UZS)</option>
            <option value="VND">Dong vietnamita (VND)</option>
            <option value="VUV">Vatu de Vanuatu (VUV)</option>
            <option value="WST">Tala samoano (WST)</option>
            <option value="XAF">Franco CFA BEAC (XAF)</option>
            <option value="XCD">Dólar del Caribe Oriental (XCD)</option>
            <option value="XOF">Franco CFA BCEAO (XOF)</option>
            <option value="XPF">Franco CFP (XPF)</option>
            <option value="YER">Rial yemení (YER)</option>
            <option value="ZAR">Rand sudafricano (ZAR)</option>
            <option value="ZMW">Kwacha zambiano (ZMW)</option>
        </select><br><br>

        <label>País:</label><br>
        <select name="pais" id="pais" required>
            <option value="">-- País --</option>
            <option value="AD">🇦🇩 Andorra</option>
            <option value="AE">🇦🇪 Emiratos Árabes Unidos</option>
            <option value="AF">🇦🇫 Afganistán</option>
            <option value="AG">🇦🇬 Antigua y Barbuda</option>
            <option value="AI">🇦🇮 Anguila</option>
            <option value="AL">🇦🇱 Albania</option>
            <option value="AM">🇦🇲 Armenia</option>
            <option value="AO">🇦🇴 Angola</option>
            <option value="AQ">🇦🇶 Antártida</option>
            <option value="AR">🇦🇷 Argentina</option>
            <option value="AS">🇦🇸 Samoa Americana</option>
            <option value="AT">🇦🇹 Austria</option>
            <option value="AU">🇦🇺 Australia</option>
            <option value="AW">🇦🇼 Aruba</option>
            <option value="AX">🇦🇽 Islas Åland</option>
            <option value="AZ">🇦🇿 Azerbaiyán</option>
            <option value="BA">🇧🇦 Bosnia y Herzegovina</option>
            <option value="BB">🇧🇧 Barbados</option>
            <option value="BD">🇧🇩 Bangladés</option>
            <option value="BE">🇧🇪 Bélgica</option>
            <option value="BF">🇧🇫 Burkina Faso</option>
            <option value="BG">🇧🇬 Bulgaria</option>
            <option value="BH">🇧🇭 Baréin</option>
            <option value="BI">🇧🇮 Burundi</option>
            <option value="BJ">🇧🇯 Benín</option>
            <option value="BL">🇧🇱 San Bartolomé</option>
            <option value="BM">🇧🇲 Bermudas</option>
            <option value="BN">🇧🇳 Brunéi</option>
            <option value="BO">🇧🇴 Bolivia</option>
            <option value="BQ">🇧🇶 Caribe Neerlandés</option>
            <option value="BR">🇧🇷 Brasil</option>
            <option value="BS">🇧🇸 Bahamas</option>
            <option value="BT">🇧🇹 Bután</option>
            <option value="BV">🇧🇻 Isla Bouvet</option>
            <option value="BW">🇧🇼 Botsuana</option>
            <option value="BY">🇧🇾 Bielorrusia</option>
            <option value="BZ">🇧🇿 Belice</option>
            <option value="CA">🇨🇦 Canadá</option>
            <option value="CC">🇨🇨 Islas Cocos</option>
            <option value="CD">🇨🇩 Congo - Kinshasa</option>
            <option value="CF">🇨🇫 República Centroafricana</option>
            <option value="CG">🇨🇬 Congo - Brazzaville</option>
            <option value="CH">🇨🇭 Suiza</option>
            <option value="CI">🇨🇮 Costa de Marfil</option>
            <option value="CK">🇨🇰 Islas Cook</option>
            <option value="CL">🇨🇱 Chile</option>
            <option value="CM">🇨🇲 Camerún</option>
            <option value="CN">🇨🇳 China</option>
            <option value="CO">🇨🇴 Colombia</option>
            <option value="CR">🇨🇷 Costa Rica</option>
            <option value="CU">🇨🇺 Cuba</option>
            <option value="CV">🇨🇻 Cabo Verde</option>
            <option value="CW">🇨🇼 Curazao</option>
            <option value="CX">🇨🇽 Isla de Navidad</option>
            <option value="CY">🇨🇾 Chipre</option>
            <option value="CZ">🇨🇿 República Checa</option>
            <option value="DE">🇩🇪 Alemania</option>
            <option value="DJ">🇩🇯 Yibuti</option>
            <option value="DK">🇩🇰 Dinamarca</option>
            <option value="DM">🇩🇲 Dominica</option>
            <option value="DO">🇩🇴 República Dominicana</option>
            <option value="DZ">🇩🇿 Argelia</option>
            <option value="EC">🇪🇨 Ecuador</option>
            <option value="EE">🇪🇪 Estonia</option>
            <option value="EG">🇪🇬 Egipto</option>
            <option value="EH">🇪🇭 Sahara Occidental</option>
            <option value="ER">🇪🇷 Eritrea</option>
            <option value="ES">🇪🇸 España</option>
            <option value="ET">🇪🇹 Etiopía</option>
            <option value="FI">🇫🇮 Finlandia</option>
            <option value="FJ">🇫🇯 Fiyi</option>
            <option value="FM">🇫🇲 Micronesia</option>
            <option value="FO">🇫🇴 Islas Feroe</option>
            <option value="FR">🇫🇷 Francia</option>
            <option value="GA">🇬🇦 Gabón</option>
            <option value="GB">🇬🇧 Reino Unido</option>
            <option value="GD">🇬🇩 Granada</option>
            <option value="GE">🇬🇪 Georgia</option>
            <option value="GF">🇬🇫 Guayana Francesa</option>
            <option value="GG">🇬🇬 Guernesey</option>
            <option value="GH">🇬🇭 Ghana</option>
            <option value="GI">🇬🇮 Gibraltar</option>
            <option value="GL">🇬🇱 Groenlandia</option>
            <option value="GM">🇬🇲 Gambia</option>
            <option value="GN">🇬🇳 Guinea</option>
            <option value="GP">🇬🇵 Guadalupe</option>
            <option value="GQ">🇬🇶 Guinea Ecuatorial</option>
            <option value="GR">🇬🇷 Grecia</option>
            <option value="GT">🇬🇹 Guatemala</option>
            <option value="GU">🇬🇺 Guam</option>
            <option value="GW">🇬🇼 Guinea-Bisáu</option>
            <option value="GY">🇬🇾 Guyana</option>
            <option value="HK">🇭🇰 Hong Kong</option>
            <option value="HM">🇭🇲 Islas Heard y McDonald</option>
            <option value="HN">🇭🇳 Honduras</option>
            <option value="HR">🇭🇷 Croacia</option>
            <option value="HT">🇭🇹 Haití</option>
            <option value="HU">🇭🇺 Hungría</option>
            <option value="ID">🇮🇩 Indonesia</option>
            <option value="IE">🇮🇪 Irlanda</option>
            <option value="IL">🇮🇱 Israel</option>
            <option value="IM">🇮🇲 Isla de Man</option>
            <option value="IN">🇮🇳 India</option>
            <option value="IO">🇮🇴 Territorio Británico del Océano Índico</option>
            <option value="IQ">🇮🇶 Irak</option>
            <option value="IR">🇮🇷 Irán</option>
            <option value="IS">🇮🇸 Islandia</option>
            <option value="IT">🇮🇹 Italia</option>
            <option value="JE">🇯🇪 Jersey</option>
            <option value="JM">🇯🇲 Jamaica</option>
            <option value="JO">🇯🇴 Jordania</option>
            <option value="JP">🇯🇵 Japón</option>
            <option value="KE">🇰🇪 Kenia</option>
            <option value="KG">🇰🇬 Kirguistán</option>
            <option value="KH">🇰🇭 Camboya</option>
            <option value="KI">🇰🇮 Kiribati</option>
            <option value="KM">🇰🇲 Comoras</option>
            <option value="KN">🇰🇳 San Cristóbal y Nieves</option>
            <option value="KP">🇰🇵 Corea del Norte</option>
            <option value="KR">🇰🇷 Corea del Sur</option>
            <option value="KW">🇰🇼 Kuwait</option>
            <option value="KY">🇰🇾 Islas Caimán</option>
            <option value="KZ">🇰🇿 Kazajistán</option>
            <option value="LA">🇱🇦 Laos</option>
            <option value="LB">🇱🇧 Líbano</option>
            <option value="LC">🇱🇨 Santa Lucía</option>
            <option value="LI">🇱🇮 Liechtenstein</option>
            <option value="LK">🇱🇰 Sri Lanka</option>
            <option value="LR">🇱🇷 Liberia</option>
            <option value="LS">🇱🇸 Lesoto</option>
            <option value="LT">🇱🇹 Lituania</option>
            <option value="LU">🇱🇺 Luxemburgo</option>
            <option value="LV">🇱🇻 Letonia</option>
            <option value="LY">🇱🇾 Libia</option>
            <option value="MA">🇲🇦 Marruecos</option>
            <option value="MC">🇲🇨 Mónaco</option>
            <option value="MD">🇲🇩 Moldavia</option>
            <option value="ME">🇲🇪 Montenegro</option>
            <option value="MF">🇲🇫 San Martín</option>
            <option value="MG">🇲🇬 Madagascar</option>
            <option value="MH">🇲🇭 Islas Marshall</option>
            <option value="MK">🇲🇰 Macedonia del Norte</option>
            <option value="ML">🇲🇱 Malí</option>
            <option value="MM">🇲🇲 Myanmar (Birmania)</option>
            <option value="MN">🇲🇳 Mongolia</option>
            <option value="MO">🇲🇴 Macao</option>
            <option value="MP">🇲🇵 Islas Marianas del Norte</option>
            <option value="MQ">🇲🇶 Martinica</option>
            <option value="MR">🇲🇷 Mauritania</option>
            <option value="MS">🇲🇸 Montserrat</option>
            <option value="MT">🇲🇹 Malta</option>
            <option value="MU">🇲🇺 Mauricio</option>
            <option value="MV">🇲🇻 Maldivas</option>
            <option value="MW">🇲🇼 Malaui</option>
            <option value="MX">🇲🇽 México</option>
            <option value="MY">🇲🇾 Malasia</option>
            <option value="MZ">🇲🇿 Mozambique</option>
            <option value="NA">🇳🇦 Namibia</option>
            <option value="NC">🇳🇨 Nueva Caledonia</option>
            <option value="NE">🇳🇪 Níger</option>
            <option value="NF">🇳🇫 Isla Norfolk</option>
            <option value="NG">🇳🇬 Nigeria</option>
            <option value="NI">🇳🇮 Nicaragua</option>
            <option value="NL">🇳🇱 Países Bajos</option>
            <option value="NO">🇳🇴 Noruega</option>
            <option value="NP">🇳🇵 Nepal</option>
            <option value="NR">🇳🇷 Nauru</option>
            <option value="NU">🇳🇺 Niue</option>
            <option value="NZ">🇳🇿 Nueva Zelanda</option>
            <option value="OM">🇴🇲 Omán</option>
            <option value="PA">🇵🇦 Panamá</option>
            <option value="PE">🇵🇪 Perú</option>
            <option value="PF">🇵🇫 Polinesia Francesa</option>
            <option value="PG">🇵🇬 Papúa Nueva Guinea</option>
            <option value="PH">🇵🇭 Filipinas</option>
            <option value="PK">🇵🇰 Pakistán</option>
            <option value="PL">🇵🇱 Polonia</option>
            <option value="PM">🇵🇲 San Pedro y Miquelón</option>
            <option value="PN">🇵🇳 Islas Pitcairn</option>
            <option value="PR">🇵🇷 Puerto Rico</option>
            <option value="PS">🇵🇸 Palestina</option>
            <option value="PT">🇵🇹 Portugal</option>
            <option value="PW">🇵🇼 Palaos</option>
            <option value="PY">🇵🇾 Paraguay</option>
            <option value="QA">🇶🇦 Catar</option>
            <option value="RE">🇷🇪 Reunión</option>
            <option value="RO">🇷🇴 Rumania</option>
            <option value="RS">🇷🇸 Serbia</option>
            <option value="RU">🇷🇺 Rusia</option>
            <option value="RW">🇷🇼 Ruanda</option>
            <option value="SA">🇸🇦 Arabia Saudita</option>
            <option value="SB">🇸🇧 Islas Salomón</option>
            <option value="SC">🇸🇨 Seychelles</option>
            <option value="SD">🇸🇩 Sudán</option>
            <option value="SE">🇸🇪 Suecia</option>
            <option value="SG">🇸🇬 Singapur</option>
            <option value="SH">🇸🇭 Santa Elena</option>
            <option value="SI">🇸🇮 Eslovenia</option>
            <option value="SJ">🇸🇯 Svalbard y Jan Mayen</option>
            <option value="SK">🇸🇰 Eslovaquia</option>
            <option value="SL">🇸🇱 Sierra Leona</option>
            <option value="SM">🇸🇲 San Marino</option>
            <option value="SN">🇸🇳 Senegal</option>
            <option value="SO">🇸🇴 Somalia</option>
            <option value="SR">🇸🇷 Surinam</option>
            <option value="SS">🇸🇸 Sudán del Sur</option>
            <option value="ST">🇸🇹 Santo Tomé y Príncipe</option>
            <option value="SV">🇸🇻 El Salvador</option>
            <option value="SX">🇸🇽 Sint Maarten</option>
            <option value="SY">🇸🇾 Siria</option>
            <option value="SZ">🇸🇿 Esuatini</option>
            <option value="TC">🇹🇨 Islas Turcas y Caicos</option>
            <option value="TD">🇹🇩 Chad</option>
            <option value="TF">🇹🇫 Territorios Australes Franceses</option>
            <option value="TG">🇹🇬 Togo</option>
            <option value="TH">🇹🇭 Tailandia</option>
            <option value="TJ">🇹🇯 Tayikistán</option>
            <option value="TK">🇹🇰 Tokelau</option>
            <option value="TL">🇹🇱 Timor Oriental</option>
            <option value="TM">🇹🇲 Turkmenistán</option>
            <option value="TN">🇹🇳 Túnez</option>
            <option value="TO">🇹🇴 Tonga</option>
            <option value="TR">🇹🇷 Turquía</option>
            <option value="TT">🇹🇹 Trinidad y Tobago</option>
            <option value="TV">🇹🇻 Tuvalu</option>
            <option value="TZ">🇹🇿 Tanzania</option>
            <option value="UA">🇺🇦 Ucrania</option>
            <option value="UG">🇺🇬 Uganda</option>
            <option value="UM">🇺🇲 Islas menores alejadas de EE. UU.</option>
            <option value="US">🇺🇸 Estados Unidos</option>
            <option value="UY">🇺🇾 Uruguay</option>
            <option value="UZ">🇺🇿 Uzbekistán</option>
            <option value="VA">🇻🇦 Ciudad del Vaticano</option>
            <option value="VC">🇻🇨 San Vicente y las Granadinas</option>
            <option value="VE">🇻🇪 Venezuela</option>
            <option value="VG">🇻🇬 Islas Vírgenes Británicas</option>
            <option value="VI">🇻🇮 Islas Vírgenes de EE. UU.</option>
            <option value="VN">🇻🇳 Vietnam</option>
            <option value="VU">🇻🇺 Vanuatu</option>
            <option value="WF">🇼🇫 Wallis y Futuna</option>
            <option value="WS">🇼🇸 Samoa</option>
            <option value="YE">🇾🇪 Yemen</option>
            <option value="YT">🇾🇹 Mayotte</option>
            <option value="ZA">🇿🇦 Sudáfrica</option>
            <option value="ZM">🇿🇲 Zambia</option>
            <option value="ZW">🇿🇼 Zimbabue</option>
        </select><br><br>


        <button type="submit">Guardar Empresa</button>
    </form>

    <script>
    function mostrarCampoOtro() {
        const select = document.getElementById('categoriaSelect');
        const campoOtro = document.getElementById('campoOtro');
        const otraCategoriaInput = document.getElementById('otraCategoriaInput');

        if (select.value === 'Otro') {
            campoOtro.style.display = 'block';
            otraCategoriaInput.required = true;
        } else {
            campoOtro.style.display = 'none';
            otraCategoriaInput.required = false;
            otraCategoriaInput.value = '';
        }
    }
    </script>
</body>

</html>