@extends('adminlte::auth.login')



@section('auth_footer')
    {{-- Si no querés nada debajo del card, podés dejar vacío --}}
@endsection

@section('css')
    <style>
        footer.login-footer {
            position: fixed;   /* Lo fija abajo */
            bottom: 0;
            left: 0;
            width: 100%;       /* Ocupa todo el ancho */
            z-index: 999;      /* Para que quede arriba de cualquier otro contenedor */
        }
    </style>
@endsection

@section('js')
    <footer class="footer login-footer mt-5 text-center bg-dark text-light py-3">
        <div class="container-fluid">
            <div class="copyright text-sm">
                Desarrollado por
                <a class="text-warning" href="https://juanchehin.github.io/ci/"><b>CI - Sistemas informáticos</b></a>
                -
                <a class="text-success"
                    href="https://wa.me/5493816188101?text=Hola%20quiero%20más%20información%20sobre%20sus%20servicios"
                    target="_blank">
                    Tel. +54 9 3816 18-8101
                </a>
            </div>
        </div>
    </footer>
@endsection
