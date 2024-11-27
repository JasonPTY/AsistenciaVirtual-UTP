<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page - UTP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/../AsistenciaVirtual/public/assets/css/principal.css"></head>
    <link rel="icon" href="/../AsistenciaVirtual/public/assets/img/logo.png">
    <style>
        body {
        font-family: 'Roboto', sans-serif;
        overflow-x: hidden;
        line-height: 1.6;
        }

        .navbar-custom {
        background-color: rgba(40, 5, 37, 0.95);
        padding: 0.8rem;
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        }

        .navbar-custom.scrolled {
            padding: 0.5rem;
            background-color: rgba(40, 5, 37, 0.98);
        }

        .navbar-custom .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .navbar-custom .navbar-brand img {
            height: 45px;
            transition: height 0.3s ease;
        }

        .navbar-custom .navbar-nav .nav-link {
            color: white;
            margin: 0 15px;
            padding: 8px 0;
            position: relative;
            transition: color 0.3s ease;
        }

        .navbar-custom .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #8753ab;
            transition: width 0.3s ease;
        }

        .navbar-custom .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        .hero-section {
            background: linear-gradient(rgba(40, 5, 37, 0.85), rgba(40, 5, 37, 0.85)), 
                        url('/../AsistenciaVirtual/public/assets/img/fondo.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 180px 0 100px;
            text-align: center;
            margin-top: 76px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, transparent 0%, rgba(40, 5, 37, 0.3) 100%);
        }

        .hero-section .container {
            position: relative;
            z-index: 2;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-section .lead {
            font-size: 1.4rem;
            margin-bottom: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-section .btn {
            padding: 12px 30px;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            background-color: #ffd700;
            color: #280525;
            border: none;
            font-weight: 600;
        }

        .hero-section .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }

        /* Secciones de contenido mejoradas */
        .section {
            padding: 100px 0;
            background: #ffffff;
            position: relative;
        }

        .section:nth-child(even) {
            background: #f8f9fa;
        }

        .section-content {
            display: flex;
            align-items: center;
            gap: 60px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-icon {
            font-size: 160px;
            color: #280525;
            opacity: 0.9;
            flex: 0 0 auto;
            transition: transform 0.3s ease;
        }

        .section-icon:hover {
            transform: scale(1.05);
        }

        .section-text {
            flex: 1;
        }

        .section h2 {
            color: #280525;
            margin-bottom: 25px;
            font-weight: 700;
            font-size: 2.5rem;
            position: relative;
            padding-bottom: 15px;
        }

        .section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: #8753ab;
        }

        /* Servicios mejorados */
        .row.mt-4 .col-md-4 {
            padding: 25px;
        }

        .row.mt-4 .col-md-4 i {
            color: #280525;
            transition: transform 0.3s ease;
        }

        .row.mt-4 .col-md-4:hover i {
            transform: translateY(-5px);
        }

        .row.mt-4 h4 {
            margin: 20px 0;
            color: #280525;
            font-weight: 600;
        }

        /* Footer mejorado */
        .footer {
            background: linear-gradient(to right, #280525, #3d0837);
            color: white;
            padding: 80px 20px 30px;
            position: relative;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, #8753ab, #ab74d2);
        }

        .footer h5 {
            color: #e5caf9;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .footer ul li {
            margin-bottom: 12px;
        }

        .footer ul li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .footer ul li a:hover {
            color: #8753ab;
            transform: translateX(5px);
        }

        .social-icons {
            margin-top: 25px;
        }

        .social-icons a {
            color: white;
            margin-right: 20px;
            font-size: 22px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .social-icons a:hover {
            color: #8753ab;
            transform: translateY(-3px);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hero-section h1 {
                font-size: 2.8rem;
            }
            
            .section {
                padding: 80px 0;
            }
            
            .section-icon {
                font-size: 140px;
            }
        }

        @media (max-width: 768px) {
            .navbar-custom .navbar-brand {
                font-size: 1rem;
            }
            
            .navbar-custom .navbar-brand img {
                height: 35px;
            }
            
            .hero-section {
                padding: 140px 20px 80px;
            }
            
            .hero-section h1 {
                font-size: 2.2rem;
            }
            
            .section-content {
                flex-direction: column !important;
                text-align: center;
                gap: 40px;
            }
            
            .section h2::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .footer {
                padding: 60px 20px 30px;
            }
            
            .footer .col-md-4 {
                margin-bottom: 40px;
                text-align: center;
            }
        }

        @media (max-width: 576px) {
            .hero-section h1 {
                font-size: 1.8rem;
            }
            
            .hero-section .lead {
                font-size: 1.1rem;
            }
        }
    </style>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="/../AsistenciaVirtual/public/assets/img/logo.png" alt="UTP Logo"> Universidad Tecnológica de Panamá
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#inicio">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#estudiantes">Estudiantes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#profesores">Profesores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#servicios">Servicios</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section id="inicio" class="hero-section">
        <div class="container">
            <h1 class="display-4">Bienvenido al Sistema de Asistencia UTP</h1>
            <p class="lead">Una plataforma integral para la gestión de asistencia académica</p>
            <div class="mt-4">
                <p>El Sistema de Asistencia de la Universidad Tecnológica de Panamá está diseñado para facilitar 
                   el registro y seguimiento de la asistencia tanto para estudiantes como profesores. 
                   Nuestra plataforma garantiza un proceso eficiente y transparente en la gestión académica.</p>
                <a href="/AsistenciaVirtual/View/login.php" class="btn btn-light btn-lg mt-3">Comenzar</a>
            </div>
        </div>
    </section>

    <section id="estudiantes" class="section">
        <div class="container">
            <div class="section-content">
                <div class="section-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="section-text">
                    <h2>ESTUDIANTES</h2>
                    <p>Nuestra plataforma ofrece a los estudiantes todas las herramientas necesarias para su éxito académico. 
                       Accede a tus registros de asistencia y más, todo en un solo lugar. Estamos aquí para apoyarte en cada 
                       paso de tu educación en la Universidad Tecnológica de Panamá</p>
                </div>
            </div>
        </div>
    </section>

    <section id="profesores" class="section">
        <div class="container">
            <div class="section-content">
                <div class="section-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="section-text">
                    <h2>PROFESORES</h2>
                    <p>Facilitamos el trabajo de los profesores con herramientas para la gestión de clases, control de 
                       asistencia y evaluación de estudiantes. Nuestra plataforma está diseñada para simplificar y 
                       mejorar la experiencia docente en la Universidad Tecnológicade de Panamá.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="servicios" class="section">
        <div class="container">
            <div class="section-content">
                <div class="section-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <div class="section-text">
                    <h2>SERVICIOS</h2>
                    <p>Nuestro sistema de asistencia virtual ofrece una solución integral para la gestión de asistencia 
                       en el aula. Con funcionalidades avanzadas, donde podrás registrar y monitorear la asistencia de 
                       manera eficiente y en tiempo real.</p>
                    <div class="row mt-4">
                        <div class="col-md-4 text-center">
                            <i class="fas fa-user-check fa-2x mb-3"style="color: #8753ab"></i>
                            <h4>Registro de Asistencia</h4>
                            <p>Automatiza el registro de asistencia de estudiantes.</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-mobile-alt fa-2x mb-3"style="color: #8753ab"></i>
                            <h4>Aplicación Móvil</h4>
                            <p>Accede al sistema desde tu dispositivo móvil en cualquier momento.</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-chart-line fa-2x mb-3" style="color: #8753ab;"></i>
                            <h4>Monitoreo en Tiempo Real</h4>
                            <p>Accede a reportes en vivo sobre la asistencia.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Universidad Tecnológica de Panamá</h5>
                    <p>Avenida Universidad Tecnológica de Panamá, Vía Puente Centenario,
                       Campus Metropolitano Víctor Levi Sasso.</p>
                </div>
                <div class="col-md-4">
                    <h5>Enlaces Rápidos</h5>
                    <ul class="list-unstyled">
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#estudiantes">Portal Estudiantes</a></li>
                        <li><a href="#profesores">Portal Profesores</a></li>
                        <li><a href="#servicios">Nuestros Servicios</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contacto</h5>
                    <p><i class="fas fa-phone"></i> +507 6210-6364</p>
                    <p><i class="fas fa-envelope"></i> soporte@utp.ac.pa</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr class="mt-4" style="background-color: rgba(255,255,255,0.1);">
            <div class="text-center mt-4">
                <p>&copy; 2024 Universidad Tecnológica de Panamá - Todos los derechos reservados</p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        $('.navbar-nav>li>a').on('click', function(){
            $('.navbar-collapse').collapse('hide');
        });
    </script>
</body>
</html>