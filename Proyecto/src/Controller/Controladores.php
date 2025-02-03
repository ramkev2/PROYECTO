<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use App\Entity\Comentario;
use App\Entity\Amistad;
use App\Entity\Publicacion;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class Controladores extends AbstractController
{
	#[Route('/login', name:'login')]
     
        public function index(Request $request,AuthenticationUtils $authenticationUtils)
        {
            
            $lastUsername = $authenticationUtils->getLastUsername();
        
           
            
        
            $lastError = $authenticationUtils->getLastAuthenticationError();
            if ($lastError) {
                $error = 'Credenciales incorrectas.';
            }
        
       
            return $this->render('login.html.twig', [
                'last_username' => $lastUsername,
                'error' => $error,
            ]);
        }
    
    
	
	#[Route('/logout', name:'logout')]
    public function logout(){    
        return new Response();
    }   
    

   
 
    #[Route('/recuperarContraseña', name:'recuperarContraseña')]

    public function recuperarContraseña(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response{    
        
    }

    #[Route('/home', name:'home')]
    public function home(Request $request, EntityManagerInterface $entityManager, UserPasswordHashedInterface $passwordHasher):Response{
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $publicaciones = $entityManager->getRepository(Publicacion::class)->findAll();

        if (!$publicaciones) {
            throw $this->createNotFoundException('Ha ocurrido un error');
        }

      
        
        return $this->render('home.html.twig', ['publicaciones'=>$publicaciones]);
    }

    public function recuperarContraseña(Request $request){   
        
        // Comprobamos si el usuario al menos se ha logueado
		//$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('recuperarContraseña.html.twig', [
            'mostrar' => false, 
            'email' => '' 
        ]);

    }

    //configurar el correo para que envie el mensaje
    #[Route('/correoRecuperacion', name:'correoRecuperacion')]	
	public function correoRecuperacion(Request $request, 
    EntityManagerInterface $entityManager, 
    UserRepository $userRepository, 
    MailerInterface $mailer){
	
        $correo = $request->request->get('_username');

        // Asegurarse de que se ha enviado el correo
        if (empty($correo)) {
            return new Response("Por favor, introduce tu correo.");
        }

        $usuario = $userRepository->findOneBy(['email' => $correo]);

        if (!$usuario) {
            return new Response("Correo no encontrado.");
        }

        //esto genera un token unico
        $codigo = random_int(100000, 999999);
        $usuario->setResetToken($codigo);
        $entityManager->persist($usuario);
        $entityManager->flush();
    
        // Enviar correo con el código
        $email = (new Email())
            ->from('Slyce')
            ->to($correo)
            ->subject('Código de Recuperación de Contraseña')
            ->text("Tu código de recuperación es: $codigo");
    
        $mailer->send($email);

        // la misma pagina pero con el input para ingresar el codigo 
        return $this->render("recuperarContraseña.html.twig", [
            'mostrar' => true,
            'email' => $correo
        ]);
    }

    //controlador para cambiar la contraseña
    #[Route('/cambioContraseña', name:'cambioContraseña')]	
	public function cambioContraseña(EntityManagerInterface $entityManager, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher)
	{
		$correo = $request->request->get('email');
		$newPass = $request->request->get('nuevaContra');
        $usuario = $userRepository->findOneBy(['email' => $correo]);

        if (!$usuario) {
            return new Response("Error: usuario no encontrado.");
        }

		$usuario->setPassword($hashedPassword);
        $usuario->setResetToken(null); // Eliminar el código
        $entityManager->persist($usuario);
        $entityManager->flush();
		return new Response("Contraseña actualizada con éxito.");
	}

    //controlador para verificar el codigo
    #[Route('/verificarCodigo', name:'verificarCodigo')]	
	public function verificarCodigo(EntityManagerInterface $entityManager, Request $request, UserRepository $userRepository)
	{
	
        $correo = $request->request->get('email');
        $codigoIngresado = $request->request->get('codigo');

        $usuario = $userRepository->findOneBy(['email' => $correo]);

        if (!$usuario || $usuario->getResetToken() != $codigoIngresado) {
            return new Response("Código incorrecto. Inténtalo de nuevo.");
        }

        // redirigir a la plantilla para que pueda cambiar la contraseña
        return $this->redirectToRoute('cambiarContraseña', ['email' => $correo]);


}
public function registrarse(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{    
    $error = null; // Inicializar variable de error

    if ($request->isMethod('POST')) {
        // Recoger los datos del formulario
        $nombre = trim($request->request->get('nombre'));
        $apellido = trim($request->request->get('apellido'));
        $email = trim($request->request->get('email'));
        $usuario = trim($request->request->get('usuario'));
        $clave = $request->request->get('clave');
        $edad = (int)$request->request->get('edad');

        // Validar que todos los campos sean proporcionados
        if (!$nombre || !$apellido || !$email || !$usuario || !$clave || !$edad) {
            $error = 'Todos los campos son obligatorios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El email no es válido.';
            return $this->render('registrarse.html.twig', [
                'error' => $error
                
            ]);
        } elseif ($edad < 14) {
            $error = 'Debes ser mayor de 14 años para registrarte.';
            return $this->render('registrarse.html.twig', [
                'error' => $error
                
            ]);
        } else {
            // Comprobar si el usuario o el email ya están registrados
            $usuarioExistente = $entityManager->getRepository(Usuario::class)->findOneBy(['usuario' => $usuario]);
            $emailExistente = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);

            if ($usuarioExistente) {
                $error = 'El nombre de usuario ya está registrado.';
                return $this->render('registrarse.html.twig', [
                    'error' => $error
                    
                ]);
            } elseif ($emailExistente) {
                $error = 'El email ya está registrado.';
                return $this->render('registrarse.html.twig', [
                    'error' => $error
                    
                ]);
            } else {
                // Crear un nuevo usuario
                $nuevoUsuario = new Usuario();
                $nuevoUsuario->setNombre($nombre);
                $nuevoUsuario->setApellido($apellido);
                $nuevoUsuario->setEmail($email);
                $nuevoUsuario->setUsuario($usuario);
                $nuevoUsuario->setEdad($edad);
                $nuevoUsuario->setRol(0);

                // Hashear la contraseña antes de guardarla
                $hashedPassword = $passwordHasher->hashPassword($nuevoUsuario, $clave);
                $nuevoUsuario->setpassword($hashedPassword);

                // Guardar el nuevo usuario en la base de datos
                $entityManager->persist($nuevoUsuario);
                $entityManager->flush();

                // Redirigir al login con mensaje de éxito
                return $this->redirectToRoute('login', ['okey' => 'Registrado correctamente']);
            }
        }
    }

    return $this->render('registrarse.html.twig', [
        'error' => $error
        
    ]);
}


    
}

