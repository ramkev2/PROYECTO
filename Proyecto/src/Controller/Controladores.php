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
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\UsuarioRepository;

class Controladores extends AbstractController
{
    private $usuarioRepository;
    private $entityManager;

    
    public function __construct(UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager)
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->entityManager = $entityManager;
    }
 

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
    
        // busco un usuario por email
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $correo]);
    
        if (!$usuario) {
            return $this->render('login.html.twig', ['error' => 'Usuario no encontrado']);
        }
    
        // verifico la contraseña
        if (!$passwordHasher->isPasswordValid($usuario, $clave)) {
            return $this->render('login.html.twig', ['error' => 'Contraseña incorrecta']);
        }
    
       return $this->render('inicio.html.twig');
    
    
    }      
   
   

    // Verificar si el usuario o email ya existen
    #[Route('/check-usuario', name: 'check-usuario', methods: ['POST'])]
    public function checkUsuario(Request $request,EntityManagerInterface $entityManager): JsonResponse
    {
        $usuario = $request->request->get('usuario');
        $email = $request->request->get('email');

        
        $existingUserByUsername = $this->entityManager->getRepository(Usuario::class)->findOneBy(['usuario' => $usuario]);

        
        $existingUserByEmail = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);

        if ($existingUserByUsername) {
            return new JsonResponse(['error' => 'Este nombre de usuario ya está en uso.'], 400);
        }

        if ($existingUserByEmail) {
            return new JsonResponse(['error' => 'Este email ya está registrado.'], 400);
        }

        return new JsonResponse(['success' => 'El nombre de usuario y el email están disponibles.'], 200);
    }

    // Registro de usuario
    #[Route('/registrarse', name: 'registrarse')]
    public function registrarse(Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        if($request->isMethod('GET')){
            return $this->render('registrarse.html.twig');
        }
        $nombre = $request->request->get('nombre');
        $apellido = $request->request->get('apellido');
        $usuario = $request->request->get('usuario');
        $email = $request->request->get('email');
        $clave = $request->request->get('clave');
        $edad = $request->request->get('edad');

       
        
        $nuevoUsuario = new Usuario();
        $nuevoUsuario->setNombre($nombre);
        $nuevoUsuario->setApellido($apellido);
        $nuevoUsuario->setUsuario($usuario);
        $nuevoUsuario->setEmail($email);
        $nuevoUsuario->setEdad($edad);
        $nuevoUsuario->setRol(0);

        
        $hashedPassword = $passwordHasher->hashPassword($nuevoUsuario, $clave);
        $nuevoUsuario->setPassword($hashedPassword);

        
        $this->entityManager->persist($nuevoUsuario);
        $this->entityManager->flush();

        return new JsonResponse(['success' => 'Usuario registrado correctamente.'], 200);
    }
    

	
	#[Route('/logout', name:'logout')]
    public function logout(){    
        return new Response();
    }   
    
    #[Route('/recuperarContraseña', name:'recuperarContraseña')]
    public function recuperarContraseña(Request $request, SessionInterface $session)
    {   
        return $this->render('/recuperarContraseña.html.twig');
    }

    #[Route("/verificarCorreo", name:'verificarCorreo')]
    public function verificarCorreo(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return new JsonResponse(['error' => 'Email no proporcionado'], 400);
        }

        $usuario = $this->usuarioRepository->findOneBy(['email' => $email]);

        if (!$usuario) {
            return new JsonResponse(['error' => 'El correo no está registrado'], 400);
        }

        return new JsonResponse(['success' => 'Correo verificado correctamente']);
    }

    
    

    #[Route('/enviarCodigo', name: 'enviarCodigo')]
    public function enviarCodigo(Request $request, MailerInterface $mailer, SessionInterface $session)
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return new JsonResponse(['error' => 'Email no proporcionado'], 400);
        }

        // Generar un código aleatorio
        $codigo = random_int(100000, 999999);
        $session->set('codigoRecuperacion', $codigo);
        $session->set('correoRecuperacion', $email);

        // Enviar el código por correo electrónico
        $emailMessage = (new Email())
            ->from('no-reply@Slyce.com')
            ->to($email)
            ->subject('Código de recuperación de contraseña')
            ->text("Tu código de recuperación es: $codigo");

        try {
            $mailer->send($emailMessage);
            return new JsonResponse(['success' => 'Código enviado correctamente']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al enviar el código: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/verificarCodigo', name: 'verificarCodigo')]
    public function verificarCodigo(Request $request, SessionInterface $session)
    {
        $codigoIngresado = $request->get('codigo');
        $codigoGuardado = $session->get('codigoRecuperacion');
        $correoGuardado = $session->get('correoRecuperacion');

        if (!$codigoGuardado || $codigoIngresado != $codigoGuardado) {
            return new JsonResponse(['error' => 'Código incorrecto. Intenta de nuevo.'], 400);
        }

        return new JsonResponse(['success' => 'Código verificado correctamente']);
    }



    #[Route('/cambiarContraseña', name: 'cambiarContraseña')]
    public function cambiarContraseña(Request $request, SessionInterface $session, UserPasswordHasherInterface $passwordHasher)
    {
        $correo = $session->get('correoRecuperacion');
        $nuevaContraseña = $request->get('newPassword');

        if (!$correo) {
            return new JsonResponse(['error' => 'No se ha encontrado el correo en la sesión.'], 400);
        }

        $usuario = $this->usuarioRepository->findOneBy(['email' => $correo]);

        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuario no encontrado.'], 400);
        }

        // Establecer la nueva contraseña
        $hashedPassword = $passwordHasher->hashPassword($usuario, $nuevaContraseña);
        $usuario->setPassword($hashedPassword);

        // Limpiar el código de la sesión
        $session->remove('codigoRecuperacion');
        $session->remove('correoRecuperacion');

        // Guardar los cambios
        $this->entityManager->persist($usuario);
        $this->entityManager->flush();

        return new JsonResponse(['success' => 'Contraseña cambiada correctamente']);
    }

}

    // #[Route('/recuperarContraseña', name:'recuperarContraseña')]
    // public function recuperarContraseña(Request $request, SessionInterface $session)
    // {   
    //     // Limpiar la sesión al entrar en la página para evitar estados inconsistentes
    //     $session->clear();

    //     return $this->render('recuperarContraseña.html.twig', [
    //         'mensaje' => $session->get('mensaje', ''),
    //     ]);
    // }
 

    // //configurar el correo para que envie el mensaje
    // #[Route('/correoRecuperacion', name:'correoRecuperacion')]
    // public function correoRecuperacion(
    //     Request $request,
    //     EntityManagerInterface $entityManager,
    //     MailerInterface $mailer, SessionInterface $session
    // ) {
    //     $correo = $request->request->get('email');

    //     // Validar que el correo no esté vacío y tenga un formato válido
    //     if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    //         return new JsonResponse(['error' => 'Por favor, introduce un correo válido.'], Response::HTTP_BAD_REQUEST);
    //     }
 
       
    //     $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $correo]);

    //     if (!$usuario) {
    //         $session->set('mensaje', 'El correo no está registrado.');
    //         return $this->redirectToRoute('recuperarContraseña');
    //     }
    
    //     // esto genera un codigo aleatorio de 6 digitos
    //     $codigo = random_int(100000, 999999);
    //     $usuario->setResetToken($codigo);
    //     $entityManager->persist($usuario);
    //     $entityManager->flush();
        
    
    //     // Enviar correo con el código
    //     $email = (new Email())
    //         ->from('noreply@Slyce.com')
    //         ->to($correo)
    //         ->subject('Código de Recuperación de Contraseña')
    //         ->text("Tu código de recuperación es: $codigo");
    
    //     $mailer->send($email);
    
    //     // Guardo los datos en la sesión
    //     $session->set('correo_recuperacion', $correo); 
    //     $session->set('mensaje', 'Correo enviado con éxito.'); 
    //     $session->set('codigoRecuperacion', $codigo);
    //     return $this->redirectToRoute('verificarCodigo');
    // }
    
    // //controlador para verificar el codigo
    // #[Route('/verificarCodigo', name:'verificarCodigo')]
    // public function verificarCodigo(Request $request, SessionInterface $session)
    // {
    //     // Si el usuario no tiene un correo en la sesión, redirigir al inicio del proceso
    //     if (!$session->has('correo_recuperacion')) {
    //         return $this->redirectToRoute('recuperarContraseña');
    //     }

    //     return $this->render('verificarCodigo.html.twig', [
    //         'email' => $session->get('correo_recuperacion'),
    //         'mensaje' => $session->get('mensaje', '')
    //     ]);
    // }

    // #[Route('/procesarCodigo', name:'procesarCodigo')]
    // public function procesarCodigo(Request $request, EntityManagerInterface $entityManager, SessionInterface $session)
    // {
    //     $correo = $request->request->get('email');
    //     $codigoIngresado = $request->request->get('codigo');

    //     $codigoGuardado = $session->get('codigoRecuperacion');

    //     if (!$codigoGuardado || $codigoIngresado != $codigoGuardado) {
    //         $session->set('mensaje', 'Código incorrecto. Intenta de nuevo.');
    //         return $this->redirectToRoute('verificarCodigo');
    //     }

        
    //     return $this->redirectToRoute('cambiarContraseña');
    // }


    // //controlador para cambiar la contraseña
    // #[Route('/cambiarContraseña', name:'cambiarContraseña')]	
	// public function cambiarContraseña(EntityManagerInterface $entityManager,Request $request,  UserPasswordHasherInterface $passwordHasher)
	// {
	// 	$correo = $request->request->get('email');
	// 	$newPass = $request->request->get('nuevaContra');

    //     dump($correo);

    //     $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $correo]);

    //     if (!$usuario) {
            
    //        return new Response("Error: usuario no encontrado.");
    //     }

    //     dump($usuario);
        
    //     $hashedPassword = $passwordHasher->hashPassword($usuario, $newPass);
	// 	$usuario->setPassword($hashedPassword);
    //     $usuario->setResetToken(null); // Eliminar el código
    //     $entityManager->persist($usuario);
    //     $entityManager->flush();
	// 	return $this->render('cambiarContraseña.html.twig');;
	// }

    

  

