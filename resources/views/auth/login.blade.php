<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Grey Farmacia — Iniciar sesión</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    .campo input:-webkit-autofill,
    .campo input:-webkit-autofill:hover,
    .campo input:-webkit-autofill:focus,
    .campo input:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 30px var(--verde-oscuro-2) inset !important;
        -webkit-text-fill-color: var(--blanco) !important;
        caret-color: var(--blanco);
        transition: background-color 5000s ease-in-out 0s;
    }

    :root{
        --verde-oscuro:#2F4F3E;
        --verde-oscuro-2:#274335;
        --verde-medio:#4B7A61;
        --verde-claro:#A9CDB4;
        --crema:#F7F5EF;
        --carbon:#242420;
        --blanco:#FFFFFF;
    }

    *{box-sizing:border-box;}

    body{
        margin:0;
        min-height:100vh;
        background:var(--verde-oscuro);
        font-family:'Inter',sans-serif;
        color:var(--carbon);
        display:flex;
        align-items:center;
        justify-content:center;
        padding:32px 16px;
        position:relative;
        overflow-x:hidden;
    }

    body::before, body::after{
        content:"";
        position:fixed;
        border-radius:50%;
        filter:blur(2px);
        background:rgba(255,255,255,0.05);
        pointer-events:none;
    }
    body::before{ width:340px; height:340px; top:-80px; left:-100px; }
    body::after{ width:260px; height:260px; bottom:-60px; right:-60px; }

    .deco{
        position:fixed;
        border-radius:50%;
        background:rgba(255,255,255,0.045);
        pointer-events:none;
    }
    .deco-1{ width:120px; height:120px; top:18%; right:8%; }
    .deco-2{ width:70px; height:70px; bottom:22%; left:6%; background:rgba(255,255,255,0.07); }
    .deco-3{ width:40px; height:40px; top:10%; left:18%; background:rgba(255,255,255,0.06); }

    .tarjeta{
        position:relative;
        z-index:1;
        display:flex;
        width:100%;
        max-width:960px;
        min-height:560px;
        background:var(--verde-oscuro-2);
        border-radius:28px;
        overflow:hidden;
        box-shadow:0 30px 70px rgba(0,0,0,0.35);
    }

    .panel-ilustracion{
        position:relative;
        flex:1 1 52%;
        background:var(--crema);
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        padding:48px;
        text-align:center;
    }

    .logo-grande{
        width:320px;
        height:320px;
        object-fit:contain;
        margin-bottom:22px;
    }

    .marca-texto{
        font-family:'Fraunces',serif;
        font-size:26px;
        font-weight:600;
        letter-spacing:.04em;
        color:var(--verde-oscuro);
        line-height:1.1;
    }
    .marca-texto span{
        display:block;
        font-family:'Inter',sans-serif;
        font-weight:500;
        font-size:12px;
        letter-spacing:.14em;
        color:var(--verde-medio);
        margin-top:6px;
    }

    .panel-form{
        flex:1 1 48%;
        padding:52px 56px;
        display:flex;
        flex-direction:column;
        justify-content:center;
        color:var(--crema);
    }

    .panel-form h1{
        font-family:'Fraunces',serif;
        font-weight:600;
        font-size:34px;
        margin:0 0 6px;
        color:var(--blanco);
    }
    .panel-form p.subtitulo{
        margin:0 0 34px;
        color:rgba(247,245,239,0.65);
        font-size:14.5px;
    }

    .campo{ margin-bottom:26px; }
    .campo label{
        display:block;
        font-size:12.5px;
        font-weight:500;
        color:rgba(247,245,239,0.7);
        margin-bottom:8px;
    }
    .campo input{
        width:100%;
        background:transparent;
        border:none;
        border-bottom:1px solid rgba(247,245,239,0.28);
        color:var(--blanco);
        font-family:'Inter',sans-serif;
        font-size:15px;
        padding:6px 2px 10px;
        outline:none;
        transition:border-color .2s ease;
    }
    .campo input::placeholder{ color:rgba(247,245,239,0.35); }
    .campo input:focus{ border-bottom-color:var(--verde-claro); }

    .btn-ingresar{
        width:100%;
        margin-top:8px;
        background:var(--verde-claro);
        color:var(--verde-oscuro-2);
        border:none;
        border-radius:999px;
        padding:14px 0;
        font-family:'Inter',sans-serif;
        font-weight:600;
        font-size:15px;
        cursor:pointer;
        transition:transform .15s ease, box-shadow .15s ease;
    }
    .btn-ingresar:hover{
        transform:translateY(-1px);
        box-shadow:0 10px 22px rgba(169,205,180,0.25);
    }
    .btn-ingresar:focus-visible{
        outline:2px solid var(--blanco);
        outline-offset:3px;
    }

    .alerta{
        background:rgba(216,96,76,0.15);
        border:1px solid rgba(216,96,76,0.4);
        color:#F3D3CC;
        font-size:13.5px;
        padding:10px 14px;
        border-radius:10px;
        margin-bottom:22px;
    }

    .pie{
        margin-top:36px;
        display:flex;
        align-items:center;
        text-align:center;
        justify-content: center;
        gap:12px;
        flex-wrap:wrap;
    }
    .pie .lema{
        font-family:'Fraunces',serif;
        font-style:italic;
        font-size:15px;
        color:rgba(247,245,239,0.45);
    }
    .pie .dev{
        font-size:12.5px;
        color:rgba(247,245,239,0.4);
        text-align: center;
    }
    .pie .dev a{ color:rgba(247,245,239,0.6); text-decoration:none; }
    .pie .dev a:hover{ text-decoration:underline; }

    @media (max-width:820px){
        .tarjeta{ flex-direction:column; max-width:420px; min-height:0; }
        .panel-ilustracion{
            min-height:220px;
            padding:36px;
        }
        .logo-grande{ width:140px; height:140px; margin-bottom:14px; }
        .marca-texto{ font-size:20px; }
        .panel-form{ padding:40px 32px 44px; }
        .panel-form h1{ font-size:28px; }
    }
</style>
</head>
<body>

<div class="deco deco-1"></div>
<div class="deco deco-2"></div>
<div class="deco deco-3"></div>

<div class="tarjeta">

    <div class="panel-ilustracion">

        <img class="logo-grande" src="{{ asset('images/logo.png') }}" alt="Grey Farmacia">

    </div>

    <div class="panel-form">

        <h1>Iniciar sesión</h1>
        <p class="subtitulo">Accede al sistema de Grey Farmacia</p>

        @if(session('error'))
            <div class="alerta">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="campo">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" placeholder="Tu nombre de usuario" required autofocus>
            </div>

            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Tu contraseña" required>
            </div>

            <button type="submit" class="btn-ingresar">Ingresar</button>
        </form>

        <div class="pie">
            <span class="lema">Cuidamos de ti, cuidamos tu salud</span>
            <span class="dev">Desarrollado por <strong>SomaAlva</strong></span>
        </div>

    </div>

</div>

</body>
</html>
