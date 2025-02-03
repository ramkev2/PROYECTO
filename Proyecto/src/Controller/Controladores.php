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
    public function login(){    
        return $this->render('login.html.twig');
    } 
    #[Route('/ctr_login', name:'ctr_login')]
    public function ctr_login(  Request $request, 
    EntityManagerInterface $entityManager, 
    UserPasswordHasherInterface $passwordHasher,)
    {
        $correo = $request->request->get('username');
        $clave = $request->request->get('password');
    
        // Buscar usuario por email
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $correo]);
    
        if (!$usuario) {
            return $this->render('login.html.twig', ['error' => 'Usuario no encontrado']);
        }
    
        // Verificar contraseña
        if (!$passwordHasher->isPasswordValid($usuario, $clave)) {
            return $this->render('login.html.twig', ['error' => 'Contraseña incorrecta']);
        }
    
       return $this->render('inicio.html.twig');
    
    
    }       
	
	#[Route('/logout', name:'logout')]
    public function logout(){    
        return new Response();
    }   
    

   
    #[Route('/registrarse', name:'registrarse')]
    public function registrarse(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher){  
        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $apellido = $request->request->get('apellido');
            $email = $request->request->get('email');
            $usuario = $request->request->get('usuario');
            $clave = $request->request->get('clave');
            $edad = (int)$request->request->get('edad');

            if (!$nombre || !$apellido || !$email || !$usuario || !$clave || !$edad) {
                $this->addFlash('error', 'Todos los campos son obligatorios.');
                return $this->redirectToRoute('registrarse');
            }

            $usuarioExistente = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);
            if ($usuarioExistente) {
                $this->addFlash('error', 'El email ya está registrado.');
                return $this->redirectToRoute('registrarse');
            }

            $nuevoUsuario = new Usuario();
            $nuevoUsuario->setNombre($nombre);
            $nuevoUsuario->setApellido($apellido);
            $nuevoUsuario->setEmail($email);
            $nuevoUsuario->setUsuario($usuario);
            $nuevoUsuario->setEdad($edad);

            $hashedPassword = $passwordHasher->hashPassword($nuevoUsuario, $clave);
            $nuevoUsuario->setClave($hashedPassword);

            $entityManager->persist($nuevoUsuario);
            $entityManager->flush();
    
            $this->addFlash('success', 'Usuario registrado con exito.');
            return $this->redirectToRoute('login');
        }
        return $this->render('registrarse.html.twig');
    }

    #[Route('/recuperarContraseña', name:'recuperarContraseña')]
    public function recuperarContraseña(Request $request){   
        
        // Comprobamos si el usuario al menos se ha logueado
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('recuperarContraseña.html.twig');
    }

    //configurar el correo para que envie el mensaje
    #[Route('/correoRecuperacion', name:'correoRecuperacion')]	
	public function correoRecuperacion(Request $request, 
    EntityManagerInterface $entityManager, 
    UserRepository $userRepository, 
    MailerInterface $mailer)
	{
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
	public function cambioContraseña(EntityManagerInterface $entityManager)
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
	public function verificarCodigo(EntityManagerInterface $entityManager)
	{
	
        $correo = $request->request->get('email');
        $codigoIngresado = $request->request->get('codigo');

        $usuario = $userRepository->findOneBy(['email' => $correo]);

        if (!$usuario || $usuario->getResetToken() != $codigoIngresado) {
            return new Response("Código incorrecto. Inténtalo de nuevo.");
        }

        // redirigir a la plantilla para que pueda cambiar la contraseña
        return $this->redirectToRoute('cambiarContraseña.html.twig', ['email' => $correo]);

	}

}
