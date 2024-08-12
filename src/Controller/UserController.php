<?php
declare(strict_types=1);
namespace App\Controller;
use App\Document\User;
use App\Security\Encoder\PasswordEncoder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Jcupitt\Vips\Image;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use function Symfony\Component\VarDumper\Dumper\esc;
class UserController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $documentManager
    ) {}
    #[Route('/users/register')]
    public function index(Request $request, PasswordEncoder $passwordEncoder): JsonResponse
    {
        $requestData=json_decode($request->getContent(), true);
        $username = $requestData['username'] ?? null;
        $password = $requestData['password'] ?? null;
        $email = $requestData['email'] ?? null;
        $user = $this->documentManager->getRepository(User::class)->findOneBy([
            '$or' => [
            ['username' => $username],
            ['email' => $email],
                ],
        ]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'message' => 'Invalid email address',
            ], Response::HTTP_BAD_REQUEST);
        }
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}$/', $password)) {
            return new JsonResponse([
                'message' => 'Password must be at least 10 characters long and contain at least one lowercase letter, one uppercase letter, one digit, and one special character',
            ], Response::HTTP_BAD_REQUEST);
        }
        if (null !== $user) {
            return new JsonResponse([
                'message' => 'User already exists',
            ], Response::HTTP_CONFLICT);
        }
        else{
            $user = new User();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($passwordEncoder->encodePassword($password));
            $user->setDateCreated(new \DateTime());
            $user->setActive(true);
            $user->setLastLogin(new \DateTime());
            $user->setLastActive(new \DateTime());
            $this->documentManager->persist($user);
            $this->documentManager->flush();
            return new JsonResponse([
                'message' => 'User crated successfully',
            ], Response::HTTP_CREATED);
        }
    }
    #[Route('/users/logins')]
    public function login(Request $request, PasswordEncoder $passwordEncoder, JWTTokenManagerInterface $jwtTokenManager): JsonResponse
    {
        $requestData=json_decode($request->getContent(), true);
        $username = $requestData['username'] ?? null;
        $password = $requestData['password'] ?? null;
        $user = $this->documentManager->getRepository(User::class)->findOneBy([
            'username' => $username
        ]);
        if ($user) {
            $password2 = $passwordEncoder->encodePassword($password);
            $password3= $user->getPassword();
            if ($password2==$password3){
                $token = $jwtTokenManager->create($user);
                return new JsonResponse([
                    'message' => 'welcome',
                    'token' => $token,
                ], Response::HTTP_CREATED);
            }
            else{
                return new JsonResponse([
                    'message' => 'Password is wrong',
                ], Response::HTTP_CREATED);
            }
        }
        else{return new JsonResponse(['message'=>'no sucha a user'], Response::HTTP_NOT_FOUND);}
    }
}
