
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .wrapper {
            position: relative;
            width: 100%;
            height: 1120px;  /* Ajustar según tamaño del fondo */
        }

        .fondo {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }

        /* Número dinámico */
        .numero {
            position: absolute;
            top: 105px;     /* AJUSTAR */
            right: 160px;   /* AJUSTAR */
            font-size: 24px;
            font-weight: bold;
            color: black;
        }
    </style>

</head>
<body>

<div class="wrapper">

    {{-- Imagen de base --}}
    <img src="{{ public_path('assets/img/recibo_base.png') }}" class="fondo">

    {{-- Número dinámico --}}
    <div class="numero">
        {{ $recibo->nro_recibo }}
    </div>

</div>

</body>
</html>
